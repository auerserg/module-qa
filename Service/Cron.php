<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Service;

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
use Superb\QA\Api\ProcessRepositoryInterface;

class Cron
{
    private $jobs = [];

    public function __construct(
        private readonly ConfigInterface $cronConfig,
        private readonly Collection $cronScheduleCollection,
        private readonly ProductMetadataInterface $productMetadata,
        private readonly TimezoneInterface $timezone,
        private readonly DateTime $dateTime,
        private readonly ProcessRepositoryInterface $processRepository,
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
     * @return array<string, array>
     */
    public function getCronJobs()
    {
        if (empty($this->jobs)) {
            foreach ($this->cronConfig->getJobs() as $jobGroup => $groupJobs) {
                foreach ($groupJobs as $job) {
                    if (isset($job['instance']) && $this->isAllowed($job['name'])) {
                        $job['group'] = $jobGroup;
                        $this->jobs[$job['name']] = $job;
                    }
                }
            }
        }

        return $this->jobs;
    }

    /**
     * @param string $cronJobName
     * @return bool
     */
    private function isAllowed($cronJobName)
    {
        if (str_starts_with($cronJobName, '_')) {
            return false;
        }
        foreach ($this->excludes as $exclude) {
            if (str_starts_with($cronJobName, $exclude)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $jobCode
     * @return array|null
     */
    public function getJobConfig($jobCode)
    {
        $jobs = $this->getCronJobs();
        return $jobs[$jobCode] ?? null;
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
        return $this->processRepository->getList($searchCriteria)->getItems();
    }

    /**
     * @param string $jobCode
     * @return Schedule
     * @throws Exception
     * @noinspection PhpDeprecationInspection
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
     * @return int
     */
    protected function getCronTimestamp()
    {
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
     * @noinspection PhpDeprecationInspection
     */
    public function updateSchedule(Schedule $schedule, $status = Schedule::STATUS_SUCCESS, $message = '')
    {
        $schedule->setStatus($status)->setFinishedAt(date('Y-m-d H:i:s', $this->getCronTimestamp()));
        $schedule->setMessages($message);
        return $schedule->save();
    }
}
