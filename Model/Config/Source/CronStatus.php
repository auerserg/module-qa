<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Model\Config\Source;

use Magento\Cron\Model\Schedule;
use Magento\Framework\Data\OptionSourceInterface;

class CronStatus implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $data = [];

        foreach ($this->toArray() as $index => $value) {
            $data[] = ['value' => $index, 'label' => $value];
        }

        return $data;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            Schedule::STATUS_PENDING => __('Pending'),
            Schedule::STATUS_RUNNING => __('Running'),
            Schedule::STATUS_SUCCESS => __('Success'),
            Schedule::STATUS_MISSED  => __('Missed'),
            Schedule::STATUS_ERROR   => __('Error'),
        ];
    }
}
