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
use Superb\QA\Model\CommandProvider;

class Commands extends Template
{

    public function __construct(
        Context $context,
        private readonly CommandProvider $commandProvider,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    public function getCommands()
    {
        $data = [];
        $commands = $this->commandProvider->getCommands();
        foreach ($commands as $command => $description) {
            $data[$command] = sprintf('%s (%s)', $command, $description);
        }
        asort($data);
        return $data;
    }

    public function getPrevious()
    {
        $data = [];
        try {
            $commands = $this->commandProvider->getPrevious();
            $customCommands = array_flip($this->commandProvider->getCustomCommands());
            foreach ($commands as $command) {
                $description = $command->getCommand();
                if (isset($customCommands[$description])) {
                    $description = $customCommands[$description];
                }
                $data[$command->getProcessId()] = $description;
            }
        } catch (Exception $e) {
            // empty
        }
        return $data;
    }

    public function getCustoms()
    {
        $data = [];
        $commands = array_keys($this->commandProvider->getCustomCommands());
        foreach ($commands as $command) {
            $data[CommandProvider::CUSTOM_PREFIX . $command] = $command;
        }
        return $data;
    }

    public function isAllowedCustomCommand()
    {
        return $this->commandProvider->isAllowedCustomCommand();
    }
}

