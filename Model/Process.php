<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Model;

use Magento\Framework\Model\AbstractModel;
use Superb\QA\Api\Data\ProcessInterface;

class Process extends AbstractModel implements ProcessInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\Process::class);
    }

    /**
     * @inheritDoc
     */
    public function getProcessId()
    {
        return $this->getData(self::PROCESS_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProcessId($processId)
    {
        return $this->setData(self::PROCESS_ID, $processId);
    }

    /**
     * @inheritDoc
     */
    public function getPid()
    {
        return $this->getData(self::PID);
    }

    /**
     * @inheritDoc
     */
    public function setPid($pid)
    {
        return $this->setData(self::PID, $pid);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function getCommand()
    {
        return $this->getData(self::COMMAND);
    }

    /**
     * @inheritDoc
     */
    public function setCommand($command)
    {
        return $this->setData(self::COMMAND, $command);
    }

    public function getLog() {
        $logDir = BP . '/var/log/qa/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        return $logDir . "command_{$this->getProcessId()}.log";
    }

    public function getCmd(): string
    {
        $logFile = $this->getLog();

        $cmd = sprintf('cd %s && nohup %s > %s 2>&1 & echo $!',
            BP,
            $this->getCommand(),
            escapeshellarg($logFile));

        return $cmd;
    }
}

