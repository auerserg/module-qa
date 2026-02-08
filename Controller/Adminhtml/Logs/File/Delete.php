<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Controller\Adminhtml\Logs\File;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteFactory;

/**
 * Controller that delete file by name.
 */
class Delete extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Superb_QA::logs_index';
    /**
     * Url to this controller
     */
    public const URL = 'qa_assistant/logs_file/delete/';

    public function __construct(
        Action\Context $context,
        private readonly Filesystem $filesystem,
        private readonly WriteFactory $writeFactory
    )
    {
        parent::__construct($context);
    }

    /**
     * Controller basic method implementation.
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('qa_assistant/logs/index');
        try {
            if (empty($fileName = $this->getRequest()->getParam('filename'))) {
                $this->messageManager->addErrorMessage(__('Please provide valid log file name'));

                return $resultRedirect;
            }
            $directory = $this->filesystem->getDirectoryWrite(DirectoryList::LOG);
            try {
                $directory->delete($directory->getAbsolutePath() . $fileName);
                $this->messageManager->addSuccessMessage(__('File %1 deleted', $fileName));
            } catch (ValidatorException $exception) {
                $this->messageManager->addErrorMessage(__('Sorry, but the data is invalid or the file is not uploaded.'));
            } catch (FileSystemException $exception) {
                $this->messageManager->addErrorMessage(__('Sorry, but the data is invalid or the file is not uploaded.'));
            }
        } catch (FileSystemException $exception) {
            $this->messageManager->addErrorMessage(__('There are no export file with such name %1', $fileName));
        }

        return $resultRedirect;
    }
}
