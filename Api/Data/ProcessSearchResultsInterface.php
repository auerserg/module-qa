<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ProcessSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get Process list.
     * @return ProcessInterface[]
     */
    public function getItems();

    /**
     * Set pid list.
     * @param ProcessInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

