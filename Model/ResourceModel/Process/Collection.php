<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Model\ResourceModel\Process;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'process_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Superb\QA\Model\Process::class,
            \Superb\QA\Model\ResourceModel\Process::class
        );
    }
}

