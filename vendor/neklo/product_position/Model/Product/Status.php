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

namespace Neklo\ProductPosition\Model\Product;

use Magento\Framework\Model\AbstractModel;

class Status extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Neklo\ProductPosition\Model\ResourceModel\Product\Status::class);
    }
}
