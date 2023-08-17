<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the NekloEULA that is bundled with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt
 *
 * Copyright (c)  Neklo (http://store.neklo.com/)
 */

namespace Neklo\ProductPosition\Model\Source\System\Config;

class Mode
{
    const MODE_LOAD_MORE_CODE = 1;
    const MODE_LOAD_MORE_LABEL = 'Yes';
    const MODE_PAGINATION_CODE = 0;
    const MODE_PAGINATION_LABEL = 'No';

    public function toOptionArray()
    {
        $valueList = $this->toArray();
        $optionArray = [];
        foreach ($valueList as $key => $value) {
            $optionArray[] = [
                'value' => $key,
                'label' => __($value),
            ];
        }
        return $optionArray;
    }

    public function toArray()
    {
        $valueList = [
            self::MODE_PAGINATION_CODE => self::MODE_PAGINATION_LABEL,
            self::MODE_LOAD_MORE_CODE => self::MODE_LOAD_MORE_LABEL
        ];
        return $valueList;
    }
}
