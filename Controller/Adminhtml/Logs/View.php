<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Controller\Adminhtml\Logs;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Superb\QA\Service\LogFile;
use Magento\Framework\Controller\Result\RedirectFactory;

class View implements HttpGetActionInterface
{
    /**
     * Url to this controller
     */
    public const URL = 'qa_assistant/logs/view/';

    public function __construct(
        private readonly PageFactory $resultPageFactory,
        private readonly LogFile $logFile,
        private readonly ManagerInterface $messageManager,
        private readonly RedirectFactory $resultRedirectFactory,
        private readonly RequestInterface $request
    )
    {
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $fileName = $this->request->getParam('filename');
        try {
            if (!$this->logFile->isExist($fileName)) {
                throw new LocalizedException(__('Please provide valid log file name'));
            }
            if (!$this->logFile->isAllowOpenFile($fileName)) {
                throw new LocalizedException(__('The file cannot be opened. File too large'));
            }
        } catch (FileSystemException $e) {
            $this->messageManager->addErrorMessage(__('Please provide valid log file name'));
            return $this->resultRedirectFactory->create()->setPath(Index::URL);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath(Index::URL);
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('QA Assistant Log View "%1"', $fileName));
        return $resultPage;
    }
}

