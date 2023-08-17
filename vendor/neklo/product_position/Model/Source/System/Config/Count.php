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

class Count implements \Magento\Framework\Option\ArrayInterface
{
    const PER_PAGE_VALUE_LIST = 'catalog/frontend/grid_per_page_values';

    const PER_PAGE_VALUE_DELIMITER = ',';
    /**
     * @var \Neklo\ProductPosition\Helper\Data
     */
    private $helper;

    public function __construct(\Neklo\ProductPosition\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    public function getPerPageValues()
    {
        $valueList = $this->helper->getConfigData(self::PER_PAGE_VALUE_LIST);
        return explode(self::PER_PAGE_VALUE_DELIMITER, $valueList);
    }

    public function toOptionArray()
    {
        $valueList = $this->getPerPageValues();
        $optionArray = [];
        foreach ($valueList as $value) {
            $optionArray[] = [
                'value' => $value,
                'label' => $value,
            ];
        }
        return $optionArray;
    }

    public function toArray()
    {
        $valueList = $this->getPerPageValues();
        $optionArray = [];
        foreach ($valueList as $value) {
            $optionArray[$value] = $value;
        }
        return $optionArray;
    }
}
