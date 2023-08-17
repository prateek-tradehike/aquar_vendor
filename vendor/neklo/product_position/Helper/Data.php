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

namespace Neklo\ProductPosition\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const GENERAL_ENABLE = 'neklo_productposition/general/is_enabled';
    const COLUMN_COUNT = 'neklo_productposition/grid/column_count';
    const ROW_COUNT = 'neklo_productposition/grid/row_count';
    const GRID_MODE = 'neklo_productposition/grid/display_mode';

    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::GENERAL_ENABLE);
    }

    public function getColumnCount()
    {
        return $this->scopeConfig->getValue(self::COLUMN_COUNT);
    }

    public function getRowCount()
    {
        return $this->scopeConfig->getValue(self::ROW_COUNT);
    }

    public function getMode()
    {
        return $this->scopeConfig->getValue(self::GRID_MODE);
    }

    public function getConfigData($key)
    {
        return $this->scopeConfig->getValue(
            $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
