<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Superb\QA\Api\Data\ProcessInterface;
use Superb\QA\Api\Data\ProcessSearchResultsInterface;

interface ProcessRepositoryInterface
{

    /**
     * Save Process
     * @param ProcessInterface $process
     * @return ProcessInterface
     * @throws LocalizedException
     */
    public function save(
        ProcessInterface $process
    );

    /**
     * Retrieve Process
     * @param string $processId
     * @return ProcessInterface
     * @throws LocalizedException
     */
    public function get($processId);

    /**
     * Retrieve Process matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return ProcessSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Process
     * @param ProcessInterface $process
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        ProcessInterface $process
    );

    /**
     * Delete Process by ID
     * @param string $processId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($processId);
}

