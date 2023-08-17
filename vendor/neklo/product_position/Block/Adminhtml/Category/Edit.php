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

namespace Neklo\ProductPosition\Block\Adminhtml\Category;

class Edit extends Position
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Neklo_ProductPosition::category/edit/js.phtml');
    }
}
