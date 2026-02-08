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
use Magento\Framework\View\Result\PageFactory;

class View implements HttpGetActionInterface
{
    /**
     * Url to this controller
     */
    public const URL = 'qa_assistant/logs/view/';
    public function __construct(
        private readonly PageFactory $resultPageFactory,
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
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('QA Assistant Log View "%1"',
            $this->request->getParam('filename')));
        return $resultPage;
    }
}

