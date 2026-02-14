<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Service;

use Exception;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Superb\QA\Api\Data\ProcessInterface;
use Superb\QA\Api\ProcessRepositoryInterface;
use Superb\QA\Model\Process;

class Commands
{
    public const CUSTOM_PREFIX = 'custom_';
    private $commands = [];

    public function __construct(
        private readonly ProcessRepositoryInterface $processRepository,
        private readonly FilterBuilder $filterBuilder,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private $excludes = [],
        private $customCommands = [],
        private $allowedCustomCommand = true
    )
    {
    }

    /**
     * @return string[]
     */
    public function getCommandsList()
    {
        return array_keys($this->getCommands());
    }

    /**
     * @return array
     * @noinspection OneTimeUseVariablesInspection
     */
    public function getCommands()
    {
        if (empty($this->commands)) {
            $application = new Cli('Magento CLI');
            foreach ($application->all() as $command) {
                if ($this->isAllowed($command->getName())) {
                    $this->commands[$command->getName()] = $command->getDescription();
                }
            }
        }

        return $this->commands;
    }

    /**
     * @param string $commandName
     * @return bool
     */
    private function isAllowed($commandName)
    {
        if (str_starts_with($commandName, '_')) {
            return false;
        }
        foreach ($this->excludes as $exclude) {
            if (str_starts_with($commandName, $exclude)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param int $count
     * @return ProcessInterface[]
     * @throws LocalizedException
     */
    public function getPrevious($count = 10)
    {
        $filter = $this->filterBuilder->setField('command')
            ->setConditionType('nlike')
            ->setValue('bin/magento cron:job:run%')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter])->setPageSize($count)->setCurrentPage(1);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        return $this->processRepository->getList($searchCriteria)->getItems();
    }


    public function getCustomCommands()
    {
        return $this->customCommands;
    }

    /**
     * @param $command
     * @param $args
     * @return int
     * @throws LocalizedException
     * @throws CouldNotSaveException
     * @throws Exception
     */
    public function run($command, $args)
    {
        if ($command) {
            $command = trim(sprintf('bin/magento %s %s', escapeshellcmd($command), escapeshellcmd($args)));
        } elseif ($this->isAllowedCustomCommand()) {
            $command = $args;
        } else {
            throw new LocalizedException(__('Command "%1" is not defined.', $args));
        }
        $entity = $this->processRepository->createNew($command);
        $pid = $this->runInBackground($entity);
        $entity->setPid($pid);
        $entity->setStatus('running');
        $this->processRepository->save($entity);
        return (int)$entity->getProcessId();
    }

    /**
     * @param $command
     * @return int
     * @throws LocalizedException
     * @throws CouldNotSaveException
     * @throws Exception
     */
    public function runCustom($command)
    {
        $command = trim($command);
        $entity = $this->processRepository->createNew($command);
        $pid = $this->runInBackground($entity);
        $entity->setPid($pid);
        $entity->setStatus('running');
        $this->processRepository->save($entity);
        return (int)$entity->getProcessId();
    }

    /**
     * @return boolean
     */
    public function isAllowedCustomCommand()
    {
        return $this->allowedCustomCommand;
    }

    /**
     * @param $processId
     * @return ProcessInterface
     * @throws LocalizedException
     */
    public function getProcess($processId)
    {
        return $this->processRepository->get($processId);
    }

    /**
     * @param int $id
     * @return int
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws Exception
     */
    public function runById(int $id)
    {
        $entity = $this->processRepository->get($id);
        $entity = $this->processRepository->createNew($entity->getCommand());
        $pid = $this->runInBackground($entity);
        $entity->setPid($pid);
        $entity->setStatus('running');
        $this->processRepository->save($entity);
        return (int)$entity->getProcessId();
    }

    /**
     * @param ProcessInterface $entity
     * @param bool             $isRunning
     * @return void
     * @throws LocalizedException
     */
    public function updateStatusProcess(ProcessInterface $entity, bool $isRunning)
    {
        $entity->setStatus($isRunning
            ? 'running'
            : 'finished');
        $this->processRepository->save($entity);
    }

    /**
     * @param Process $entity
     * @return int
     * @throws Exception
     */
    public function runInBackground(Process $entity)
    {
        $command = $entity->getCmd();
        exec($command);
        $pid = (int)file_get_contents($entity->getPidLog());
        if ($pid > 0) {
            unlink($entity->getPidLog());
        }

        return $pid;
    }
}
