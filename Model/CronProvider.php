<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Model;

use Exception;
use Magento\Cron\Model\ConfigInterface;
use Magento\Cron\Model\ResourceModel\Schedule\Collection;
use Magento\Cron\Model\Schedule;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Superb\QA\Api\Data\ProcessInterface;

class CronProvider
{
    private $jobs = [];

    public function __construct(
        private readonly ConfigInterface $cronConfig,
        private readonly Collection $cronScheduleCollection,
        private readonly ProductMetadataInterface $productMetadata,
        private readonly TimezoneInterface $timezone,
        private readonly DateTime $dateTime,
        private readonly ProcessRepository $processRepository,
        private readonly FilterBuilder $filterBuilder,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private $excludes = [],
    )
    {
    }

    /**
     * @return int[]|string[]
     */
    public function getCronJobsList()
    {
        return array_keys($this->getCronJobs());
    }

    /**
     * @return array
     */
    public function getCronJobs()
    {
        if (empty($this->jobs)) {
            $jobs = $this->cronConfig->getJobs();
            foreach ($jobs as $jobGroup => $groupJobs) {
                foreach ($groupJobs as $job) {
                    if ($this->isAllowed($job['name'])) {
                        $job['group'] = $jobGroup;
                        $this->jobs[$job['name']] = $job;
                    }
                }
            }
        }

        return $this->jobs;
    }

    /**
     * @param $jobCode
     * @return mixed|null
     */
    public function getJobConfig($jobCode)
    {
        $jobs = $this->getCronJobs();
        return isset($jobs[$jobCode])
            ? $jobs[$jobCode]
            : null;
    }

    /**
     * @param int $count
     * @return ProcessInterface[]
     * @throws LocalizedException
     */
    public function getPrevious($count = 10)
    {
        $filter = $this->filterBuilder->setField('command')
            ->setConditionType('like')
            ->setValue('bin/magento cron:job:run%')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter])->setPageSize($count)->setCurrentPage(1);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $results = $this->processRepository->getList($searchCriteria);

        return $results->getItems();
    }

    /**
     * @param string $jobCode
     * @return Schedule
     * @throws Exception
     */
    public function createNewSchedule($jobCode)
    {
        /* @var $schedule Schedule */
        $schedule = $this->cronScheduleCollection->getNewEmptyItem();
        $schedule->setJobCode($jobCode)->setStatus(Schedule::STATUS_RUNNING)->setExecutedAt(date('Y-m-d H:i:s',
            $this->getCronTimestamp()))->save();
        return $schedule;
    }

    /**
     * Get timestamp used for time related database fields in the cron tables
     *
     * Note: The timestamp used will change from Magento 2.1.7 to 2.2.0 and
     *       these changes are branched by Magento version in this method.
     *
     * @return int
     */
    protected function getCronTimestamp()
    {
        /* @var $version string e.g. "2.1.7" */
        $version = $this->productMetadata->getVersion();

        if (version_compare($version, '2.2.0') >= 0) {
            return $this->dateTime->gmtTimestamp();
        }

        return $this->timezone->scopeTimeStamp();
    }

    /**
     * @param Schedule $schedule
     * @param string   $status
     * @param string   $message
     * @return Schedule
     * @throws Exception
     */
    public function updateSchedule(Schedule $schedule, $status = Schedule::STATUS_SUCCESS, $message = '')
    {
        $schedule->setStatus($status)->setFinishedAt(date('Y-m-d H:i:s', $this->getCronTimestamp()));
        $schedule->setMessages($message);
        return $schedule->save();
    }


    /**
     * @param string $cronJobName
     * @return bool
     */
    private function isAllowed($cronJobName)
    {
        if (strpos($cronJobName, '_') === 0) {
            return false;
        }
        foreach ($this->excludes as $exclude) {
            if (strpos($cronJobName, $exclude) === 0) {
                return false;
            }
        }
        return true;
    }

}
