<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Model;

use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Superb\QA\Api\Data\ProcessInterface;
use Superb\QA\Api\Data\ProcessInterfaceFactory;
use Superb\QA\Api\Data\ProcessSearchResultsInterface;
use Superb\QA\Api\Data\ProcessSearchResultsInterfaceFactory;
use Superb\QA\Api\ProcessRepositoryInterface;
use Superb\QA\Model\ResourceModel\Process as ResourceProcess;
use Superb\QA\Model\ResourceModel\Process\CollectionFactory as ProcessCollectionFactory;

class ProcessRepository implements ProcessRepositoryInterface
{
    public function __construct(
        private readonly ResourceProcess $resource,
        private readonly ProcessInterfaceFactory $processFactory,
        private readonly ProcessCollectionFactory $processCollectionFactory,
        private readonly ProcessSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    )
    {
    }

    /**
     * @param $command
     * @return ProcessInterface
     * @throws CouldNotSaveException
     */
    public function createNew($command)
    {
        /** @var ProcessInterface $process */
        $process = $this->processFactory->create();

        $process->setCommand($command);
        $process->setStatus('pending');

        try {
            $this->save($process);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__('Could not create new process: %1', $exception->getMessage()));
        }

        return $process;
    }

    /**
     * @param ProcessInterface $process
     * @return ProcessInterface
     * @throws CouldNotSaveException
     */
    public function save(ProcessInterface $process)
    {
        try {
            $this->resource->save($process);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__('Could not save the process: %1',
                $exception->getMessage()));
        }
        return $process;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return ProcessSearchResultsInterface
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function getList(
        SearchCriteriaInterface $criteria
    )
    {
        $collection = $this->processCollectionFactory->create();

        $collection->setOrder('process_id', 'DESC');

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param $processId
     * @return bool
     * @throws LocalizedException
     */
    public function deleteById($processId)
    {
        return $this->delete($this->get($processId));
    }

    /**
     * @param ProcessInterface $process
     * @return true
     * @throws CouldNotDeleteException
     */
    public function delete(ProcessInterface $process)
    {
        try {
            $processModel = $this->processFactory->create();
            $this->resource->load($processModel, $process->getProcessId());
            $this->resource->delete($processModel);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the Process: %1',
                $exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $processId
     * @return ProcessInterface
     * @throws NoSuchEntityException
     */
    public function get($processId)
    {
        $process = $this->processFactory->create();
        $this->resource->load($process, $processId);
        if (!$process->getId()) {
            throw new NoSuchEntityException(__('Process with id "%1" does not exist.', $processId));
        }
        return $process;
    }
}

