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
use Magento\ImportExport\Model\LocalizedFileName;
use Magento\Ui\Component\Listing\Columns\Column;
use Superb\QA\Controller\Adminhtml\Logs\View;

/**
 * File display name column
 */
class FileDisplayName extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly LocalizedFileName $localizedFileName,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        $fieldName = $this->getData('name');
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$fieldName] = sprintf('<a href="%s">%s</a>',
                    $this->urlBuilder->getUrl(View::URL,
                        ['_query' => ['filename' => $item['file_name']]]),
                    $this->localizedFileName->getFileDisplayName($item['file_name']));
            }
        }

        return $dataSource;
    }
}
