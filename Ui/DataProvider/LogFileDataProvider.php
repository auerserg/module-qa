<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Ui\DataProvider;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

/**
 * Data provider for log grid.
 */
class LogFileDataProvider extends DataProvider
{
    /**
     * @var File|null
     */
    private $fileIO;

    /**
     * @var DriverInterface
     */
    private $file;

    /**
     * @var WriteInterface
     */
    private $directory;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @param string                $name
     * @param string                $primaryFieldName
     * @param string                $requestFieldName
     * @param ReportingInterface    $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface      $request
     * @param FilterBuilder         $filterBuilder
     * @param DriverInterface       $file
     * @param Filesystem            $filesystem
     * @param File|null             $fileIO
     * @param array                 $meta
     * @param array                 $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @throws FileSystemException
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        DriverInterface $file,
        Filesystem $filesystem,
        File $fileIO = null,
        array $meta = [],
        array $data = []
    )
    {
        $this->file = $file;
        $this->fileSystem = $filesystem;
        parent::__construct($name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data);

        $this->fileIO = $fileIO
            ? : ObjectManager::getInstance()->get(File::class);
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::LOG);
    }

    /**
     * Returns data for grid.
     *
     * @return array
     * @throws FileSystemException
     */
    public function getData()
    {
        $emptyResponse = ['items' => [], 'totalRecords' => 0];
        if (!$this->directory->isExist($this->directory->getAbsolutePath())) {
            return $emptyResponse;
        }

        $files = $this->getLogFiles($this->directory->getAbsolutePath());
        if (empty($files)) {
            return $emptyResponse;
        }
        $data = [];
        foreach ($files as $file) {
            $data['items'][]['file_name'] = $this->getPathToLogFile($this->fileIO->getPathInfo($file));
        }

        $paging = $this->request->getParam('paging');
        $pageSize = (int)($paging['pageSize'] ?? 0);
        $pageCurrent = (int)($paging['current'] ?? 0);
        $pageOffset = ($pageCurrent - 1) * $pageSize;
        $data['totalRecords'] = count($data['items']);
        $data['items'] = array_slice($data['items'], $pageOffset, $pageSize);

        return $data;
    }

    /**
     * Get files from directory path, sort them by date modified and return sorted array of full path to files
     *
     * @param string $directoryPath
     * @return array
     * @throws FileSystemException
     */
    private function getLogFiles(string $directoryPath): array
    {
        $sortedFiles = [];
        $files = $this->directory->getDriver()->readDirectoryRecursively($directoryPath);
        if (empty($files)) {
            return [];
        }
        foreach ($files as $filePath) {
            $filePath = $this->directory->getAbsolutePath($filePath);
            if ($this->directory->isFile($filePath)) {
                $fileModificationTime = $this->directory->stat($filePath)['mtime'];
                $sortedFiles[$filePath] = $fileModificationTime;
            }
        }
        //sort array elements using key value
        arsort($sortedFiles);

        return array_keys($sortedFiles);
    }

    /**
     * Return relative log file path after "var/log"
     *
     * @param mixed $file
     * @return string
     */
    private function getPathToLogFile($file): string
    {
        $delimiter = '/';
        $cutPath = explode($delimiter,
            $this->directory->getAbsolutePath());

        $filePath = explode($delimiter,
            $file['dirname'] ?? '');

        return ltrim(implode($delimiter, array_diff($filePath, $cutPath)) . $delimiter . $file['basename'],
            $delimiter);
    }
}
