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
use Superb\QA\Service\Cron as ServiceCron;

class Cron extends Template
{

    /**
     * Constructor
     *
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Context $context,
        private readonly ServiceCron $cronService,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * @return array<string,string>
     */
    public function getCronJobs(): array
    {
        $this->cronService->getCronJobs();
        $data = [];
        foreach ($this->cronService->getCronJobs() as $name => $job) {
            $data[$name] = str_replace(' ()', '', sprintf('%s (%s)', $job['name'], $job['schedule'] ?? ''));
        }
        asort($data);
        return $data;
    }

    /**
     * @return array<string,string>
     * @noinspection PhpUnusedLocalVariableInspection
     */
    public function getPrevious(): array
    {
        $data = [];
        try {
            foreach ($this->cronService->getPrevious() as $command) {
                $data[$command->getProcessId()] = trim(str_replace('bin/magento cron:job:run',
                    '',
                    $command->getCommand()));
            }
            $data = array_filter($data);
        } catch (Exception $e) {
            // empty
        }
        return $data;
    }
}

