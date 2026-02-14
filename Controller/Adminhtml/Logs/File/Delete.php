<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Controller\Adminhtml\Logs\File;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Superb\QA\Controller\Adminhtml\Logs\Index;
use Superb\QA\Service\LogFile;

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
        private readonly LogFile $logFile,
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
        try {
            $fileName = $this->getRequest()->getParam('filename');
            $this->logFile->deleteFile($fileName);
            $this->messageManager->addSuccessMessage(__('File %1 deleted', $fileName));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath(Index::URL);
    }
}
