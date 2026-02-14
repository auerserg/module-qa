<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Model;

use Exception;
use Magento\Framework\Model\AbstractModel;
use Superb\QA\Api\Data\ProcessInterface;

class Process extends AbstractModel implements ProcessInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModel\Process::class);
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
    public function setCommand($command)
    {
        return $this->setData(self::COMMAND, $command);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getCmd(): string
    {
        $logFile = $this->getLog();
        $command = $this->getCommand();
        $pidFile = $this->getPidLog();
        $prompt = sprintf('root@host:~$ %s', $command);

        $cmd = sprintf('cd %s && (echo "%s" >> %s; nohup %s >> %s 2>&1 & echo $! > %s)',
            BP,
            addslashes($prompt),
            escapeshellarg($logFile),
            $command,
            escapeshellarg($logFile),
            escapeshellarg($pidFile));

        return $cmd;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getLog()
    {
        $logDir = $this->getQADir();
        return $logDir . "command_{$this->getProcessId()}.log";
    }

    /**
     * @return string
     * @throws Exception
     * @noinspection ThrowRawExceptionInspection
     */
    private function getQADir()
    {
        $logDir = BP . '/var/log/qa/';
        if (!is_dir($logDir) && !mkdir($logDir, 0775, true) && !is_dir($logDir)) {
            throw new Exception('QA Directory was not created');
        }
        return $logDir;
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
    public function getCommand()
    {
        return $this->getData(self::COMMAND);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getPidLog()
    {
        $logDir = $this->getQADir();
        return $logDir . "command_{$this->getProcessId()}.pid";
    }
}

