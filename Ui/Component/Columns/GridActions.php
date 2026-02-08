<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Ui\Component\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Superb\QA\Controller\Adminhtml\Logs\File\Delete;
use Superb\QA\Controller\Adminhtml\Logs\File\Download;
use Superb\QA\Controller\Adminhtml\Logs\View;

/**
 * Actions for export grid.
 */
class GridActions extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['file_name'])) {
                    $filename = $item['file_name'];
                    $item[$name]['view'] = [
                        'href'  => $this->urlBuilder->getUrl(View::URL,
                            ['_query' => ['filename' => $filename]]),
                        'label' => __('View')
                    ];
                    $item[$name]['download'] = [
                        'href'  => $this->urlBuilder->getUrl(Download::URL,
                            ['_query' => ['filename' => $filename]]),
                        'label' => __('Download')
                    ];
                    $item[$name]['delete'] = [
                        'href'    => $this->urlBuilder->getUrl(Delete::URL,
                            ['_query' => ['filename' => $filename]]),
                        'label'   => __('Delete'),
                        'confirm' => [
                            'title'   => __('Delete'),
                            'message' => __('Are you sure you wan\'t to delete a file?')
                        ],
                    ];
                }
            }
        }
        return $dataSource;
    }
}
