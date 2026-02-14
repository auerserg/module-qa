<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Api\Data;

interface ProcessInterface
{

    public const UPDATED_AT = 'updated_at';
    public const COMMAND = 'command';
    public const CREATED_AT = 'created_at';
    public const PROCESS_ID = 'process_id';
    public const STATUS = 'status';
    public const PID = 'pid';

    /**
     * Get process_id
     * @return string|null
     */
    public function getProcessId();

    /**
     * Set process_id
     * @param string $processId
     * @return ProcessInterface
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
     * @return ProcessInterface
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
     * @return ProcessInterface
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
     * @return ProcessInterface
     */
    public function setCommand($command);
}

