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
use Superb\QA\Service\Cron;

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
        private readonly Cron $cronProvider,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    public function getCronJobs()
    {
        $this->cronProvider->getCronJobs();
        $data = [];
        $jobs = $this->cronProvider->getCronJobs();
        foreach ($jobs as $name => $job) {
            $data[$name] = str_replace(' ()', '', sprintf('%s (%s)', $job['name'], $job['schedule'] ?? ''));
        }
        asort($data);
        return $data;
    }

    public function getPrevious()
    {
        $data = [];
        try {
            $commands = $this->cronProvider->getPrevious();
            foreach ($commands as $command) {
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

