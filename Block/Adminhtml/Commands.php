<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Block\Adminhtml;

use Exception;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Superb\QA\Service\Commands as CommandsService;

class Commands extends Template
{

    public function __construct(
        Context $context,
        private readonly CommandsService $commandsService,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * @return array<string, string>
     */
    public function getCommands()
    {
        $data = [];
        foreach ($this->commandsService->getCommands() as $command => $description) {
            $data[$command] = sprintf('%s (%s)', $command, $description);
        }
        asort($data);
        return $data;
    }

    /**
     * @return array<string, string>
     * @noinspection PhpUnusedLocalVariableInspection
     */
    public function getPrevious()
    {
        $data = [];
        try {
            $commands = $this->commandsService->getPrevious();
            $customCommands = array_flip($this->commandsService->getCustomCommands());
            foreach ($commands as $command) {
                $description = $command->getCommand();
                $data[$command->getProcessId()] = $customCommands[$description] ?? $description;
            }
        } catch (Exception $e) {
            // empty
        }
        return $data;
    }

    /**
     * @return array<string, string>
     */
    public function getCustoms()
    {
        $data = [];
        foreach (array_keys($this->commandsService->getCustomCommands()) as $command) {
            $data[CommandsService::CUSTOM_PREFIX . $command] = $command;
        }
        return $data;
    }

    /**
     * @return bool
     */
    public function isAllowedCustomCommand()
    {
        return $this->commandsService->isAllowedCustomCommand();
    }
}

