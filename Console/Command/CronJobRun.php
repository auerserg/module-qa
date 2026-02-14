<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Console\Command;

use Exception;
use InvalidArgumentException;
use Magento\Cron\Model\Schedule;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\ObjectManagerInterface;
use RuntimeException;
use Superb\QA\Service\Cron;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronJobRun extends Command
{

    private const NAME_ARGUMENT = "job";

    public function __construct(
        private readonly State $state,
        private readonly ObjectManagerInterface $objectManager,
        private readonly Cron $cronProvider,
        ?string $name = null
    )
    {
        parent::__construct($name);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws LocalizedException
     * @throws Exception
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int
    {
        $this->state->setAreaCode(Area::AREA_CRONTAB);
        $configLoader = $this->objectManager->get(ConfigLoaderInterface::class);
        $this->objectManager->configure($configLoader->load(Area::AREA_CRONTAB));

        $areaList = $this->objectManager->get(AreaList::class);
        $areaList->getArea(Area::AREA_CRONTAB)->load(Area::PART_CONFIG)->load(Area::PART_TRANSLATE);
        $output->writeln("Cronjob");
        $jobCode = $input->getArgument(self::NAME_ARGUMENT);

        if (!$jobCode) {
            throw new InvalidArgumentException('Invalid job');
        }

        $jobConfig = $this->cronProvider->getJobConfig($jobCode);

        if (empty($jobCode) || !isset($jobConfig['instance'])) {
            throw new InvalidArgumentException('No job config found!');
        }

        $model = $this->objectManager->get($jobConfig['instance']);

        if (!$model || !is_callable([$model, $jobConfig['method']])) {
            throw new RuntimeException(sprintf('Invalid callback: %s::%s does not exist',
                $jobConfig['instance'],
                $jobConfig['method']));
        }

        $output->writeln("Job code: {$jobCode}");
        $output->writeln('Run ' . $jobConfig['instance'] . '::' . $jobConfig['method']);
        $schedule = $this->cronProvider->createNewSchedule($jobCode);
        try {
            $model->{$jobConfig['method']}($schedule);
            $this->cronProvider->updateSchedule($schedule);
        } catch (Exception $e) {
            $this->cronProvider->updateSchedule($schedule, Schedule::STATUS_ERROR, $e->getMessage());
        }

        if (isset($e)) {
            throw new RuntimeException(sprintf("Cron-job '%s' threw exception %s\n%s\n%s",
                $jobCode,
                get_class($e),
                $e->getMessage(),
                $e->getTraceAsString()), 0, $e);
        }

        $output->writeln('done');
        return Command::SUCCESS;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName("cron:job:run");
        $this->setDescription("Run job");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Runs a cronjob by job code")
        ]);
        parent::configure();
    }
}
