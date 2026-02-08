<?php
/**
 * Copyright © Auer, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Controller\Adminhtml\Logs\File;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\LocalizedFileName;
use Throwable;

class Download extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Superb_QA::logs_index';
    /**
     * Url to this controller
     */
    public const URL = 'qa_assistant/logs_file/download/';
    protected $messageManager;

    /**
     * @var LocalizedFileName
     */
    private $localizedFileName;

    public function __construct(
        Action\Context $context,
        private readonly FileFactory $fileFactory,
        private readonly Filesystem $filesystem,
        ?LocalizedFileName $localizedFileName = null
    )
    {
        parent::__construct($context);
        $this->localizedFileName = $localizedFileName ?? ObjectManager::getInstance()->get(LocalizedFileName::class);
    }

    /**
     * Controller basic method implementation.
     *
     * @return Redirect|ResponseInterface
     * @throws FileSystemException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('qa_assistant/logs/index');

        $fileName = $this->getRequest()->getParam('filename');

        if (empty($fileName)) {
            $this->messageManager->addErrorMessage(__('Please provide valid log file name'));

            return $resultRedirect;
        }

        $logDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::LOG);

        try {
            $fileName = $logDirectory->getDriver()->getRealPathSafety($fileName);
            $fileExist = $logDirectory->isExist($fileName);
        } catch (Throwable $e) {
            $fileExist = false;
        }
        if (empty($fileName) || !$fileExist) {
            $this->messageManager->addErrorMessage(__('Please provide valid log file name'));

            return $resultRedirect;
        }

        try {
            $directory = $this->filesystem->getDirectoryRead(DirectoryList::LOG);
            if ($directory->isFile($fileName)) {
                return $this->fileFactory->create($this->localizedFileName->getFileDisplayName($fileName),
                    ['type' => 'filename', 'value' => $fileName],
                    DirectoryList::LOG);
            }
            $this->messageManager->addErrorMessage(__('%1 is not a valid file', $fileName));
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        return $resultRedirect;
    }
}
