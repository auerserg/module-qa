<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\FileSystemException;
use Superb\QA\Controller\Adminhtml\Logs\File\Delete;
use Superb\QA\Controller\Adminhtml\Logs\File\Download;
use Superb\QA\Controller\Adminhtml\Logs\Index;
use Superb\QA\Service\LogFile;

class LogsView extends Template
{

    /**
     * Constructor
     *
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Context $context,
        private readonly LogFile $logFile,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getActions()
    {
        $filename = $this->getRequest()->getParam('filename');
        $actions = [];
        $actions['back'] = [
            'href'  => $this->_urlBuilder->getUrl(Index::URL),
            'label' => __('Back')
        ];
        $actions['download'] = [
            'href'  => $this->_urlBuilder->getUrl(Download::URL,
                ['_query' => ['filename' => $filename]]),
            'label' => __('Download')
        ];
        $actions['delete'] = [
            'href'    => $this->_urlBuilder->getUrl(Delete::URL,
                ['_query' => ['filename' => $filename]]),
            'label'   => __('Delete'),
            'confirm' => [
                'title'   => __('Delete'),
                'message' => __('Are you sure you wan\'t to delete a file?')
            ],
        ];
        return $actions;
    }

    /**
     * @return string
     * @noinspection PhpUnusedLocalVariableInspection
     */
    public function getContent()
    {
        $fileName = $this->getRequest()->getParam('filename');
        try {
            return $this->logFile->getContentFile($fileName);
        } catch (FileSystemException $e) {
            return '';
        }
    }
}

