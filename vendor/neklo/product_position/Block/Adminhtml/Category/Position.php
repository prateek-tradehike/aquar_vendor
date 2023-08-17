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

use Neklo\ProductPosition\Model\Source\System\Config\Mode;
use Magento\Backend\Block\Template;

class Position extends Template
{
    /**
     * @var \Neklo\ProductPosition\Helper\Product
     */
    private $helperProduct;

    /**
     * @var \Neklo\ProductPosition\Helper\Data
     */
    private $config;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    public function __construct(
        \Neklo\ProductPosition\Helper\Product $helperProduct,
        \Neklo\ProductPosition\Helper\Data $helperConfig,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->helperProduct = $helperProduct;
        $this->config = $helperConfig;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->setTemplate('Neklo_ProductPosition::category/tab/product/position.phtml');
        parent::_construct();

        $mode = $this->config->getMode();
        $this->setData('show_mode', $mode);
        switch ($mode) {
            case Mode::MODE_PAGINATION_CODE:
                $this->setData('mode_class', 'Pager');
                break;
            default:
                $this->setData('mode_class', 'Sorter');
                break;
        }
    }

    /**
     * @return bool
     */
    public function canShow()
    {
        return $this->helperProduct->isEnabled();
    }

    /**
     * @return int
     */
    public function getColumnCount()
    {
        return $this->helperProduct->getColumnCount();
    }

    /**
     * @return int
     */
    public function getRowCount()
    {
        return $this->helperProduct->getRowCount();
    }

    /**
     * @return int
     */
    public function getPerPageCount()
    {
        return $this->helperProduct->getPerPageCount();
    }

    /**
     * @return array
     */
    public function getCollectionJson()
    {
        return $this->helperProduct->getCollectionJson();
    }

    /**
     * @return string
     */
    public function getSortedProductsPositionJson()
    {
        return $this->helperProduct->getSortedProductsPositionJson();
    }

    /**
     * @return string
     */
    public function getAttachedProductsJson()
    {
        return $this->helperProduct->getAttachedProductsJson();
    }

    /**
     * @return string
     */
    public function getCollectionSize()
    {
        return $this->helperProduct->getCollection()->getSize();
    }

    /**
     * @return string
     */
    public function getNextPageUrl()
    {
        $params = [];
        if ($this->getCategory() && $this->getCategory()->getId()) {
            $params['id'] = $this->getCategory()->getId();
        }
        return $this->getUrl('neklo_productposition/ajax/page', $params);
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        $category = $this->coreRegistry->registry('current_category');
        return $category;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}
