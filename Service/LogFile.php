<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Service;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\ImportExport\Model\LocalizedFileName;
use Throwable;

class LogFile
{
    /**
     * @var LocalizedFileName
     */
    protected $localizedFileName;

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly WriteFactory $writeFactory
    )
    {
    }

    /**
     * @param $fileName
     * @return bool
     * @throws LocalizedException
     */
    public function deleteFile($fileName)
    {
        try {
            if ($this->isExist($fileName)) {
                throw new LocalizedException(__('Please provide valid log file name'));
            }
            $directory = $this->getDirectoryWrite();
            return $directory->delete($directory->getAbsolutePath() . $fileName);
        } catch (ValidatorException $exception) {
            throw new LocalizedException(__('Sorry, but the data is invalid or the file is not uploaded.'));
        } catch (FileSystemException $exception) {
            throw new LocalizedException(__('Sorry, but the data is invalid or the file is not uploaded.'));
        }
    }

    /**
     * @param $fileName
     * @return bool
     * @throws FileSystemException
     */
    public function isExist(&$fileName)
    {
        if (empty($fileName)) {
            return false;
        }
        $directory = $this->getDirectoryWrite();
        try {
            $fileName = $directory->getDriver()->getRealPathSafety($fileName);
            $fileExist = $directory->isExist($fileName) && $directory->isFile($fileName);
        } catch (Throwable $e) {
            $fileExist = false;
        }
        return $fileExist;
    }

    /**
     * @return Filesystem\Directory\WriteInterface
     * @throws FileSystemException
     */
    private function getDirectoryWrite()
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::LOG);
    }

    /**
     * @param $fileName
     * @return string
     * @throws FileSystemException
     */
    public function getContentFile($fileName)
    {
        $directory = $this->getDirectoryRead();
        $filePath = $directory->getAbsolutePath($fileName);
        return $directory->readFile($filePath);
    }

    /**
     * @return Filesystem\Directory\ReadInterface
     */
    private function getDirectoryRead()
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::LOG);
    }

    public function isAllowOpenFile($fileName)
    {
        $stats = $this->stats($fileName);
        $limit = $this->getLimit();
        return $limit > $stats['size'];
    }

    /**
     * @param string $fileName
     * @return array
     */
    public function stats($fileName)
    {
        $directory = $this->getDirectoryRead();
        return $directory->stat($fileName);
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        $memory_limit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
            switch ($matches[2]) {
                case 'G':
                    $memory_limit = $matches[1] * 1024 * 1024 * 1024;
                    break;
                case 'M':
                    $memory_limit = $matches[1] * 1024 * 1024;
                    break;
                case 'K':
                    $memory_limit = $matches[1] * 1024;
                    break;
            }
        }
        return (int)floor($memory_limit * 0.8);
    }

}
