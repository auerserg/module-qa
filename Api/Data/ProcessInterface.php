<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Api\Data;

interface ProcessInterface
{

    const UPDATED_AT = 'updated_at';
    const COMMAND = 'command';
    const CREATED_AT = 'created_at';
    const PROCESS_ID = 'process_id';
    const STATUS = 'status';
    const PID = 'pid';

    /**
     * Get process_id
     * @return string|null
     */
    public function getProcessId();

    /**
     * Set process_id
     * @param string $processId
     * @return \Superb\QA\Process\Api\Data\ProcessInterface
     */
    public function setProcessId($processId);

    /**
     * Get pid
     * @return string|null
     */
    public function getPid();

    /**
     * Set pid
     * @param string $pid
     * @return \Superb\QA\Process\Api\Data\ProcessInterface
     */
    public function setPid($pid);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Superb\QA\Process\Api\Data\ProcessInterface
     */
    public function setStatus($status);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Get command
     * @return string|null
     */
    public function getCommand();

    /**
     * Get log path
     * @return string
     */
    public function getLog();
    /**
     * Get command cmd
     * @return string
     */
    public function getCmd();

    /**
     * Set command
     * @param string $command
     * @return \Superb\QA\Process\Api\Data\ProcessInterface
     */
    public function setCommand($command);
}

