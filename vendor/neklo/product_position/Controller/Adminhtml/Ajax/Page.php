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

namespace Neklo\ProductPosition\Controller\Adminhtml\Ajax;

class Page extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Magento_Catalog::categories';
    /**
     * @var \Neklo\ProductPosition\Helper\Product
     */
    private $helperProduct;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Neklo\ProductPosition\Helper\Data
     */
    private $config;

    /**
     * Page constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Neklo\ProductPosition\Helper\Product $helperProduct
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Neklo\ProductPosition\Helper\Data $config
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Neklo\ProductPosition\Helper\Product $helperProduct,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Neklo\ProductPosition\Helper\Data $config
    ) {
        parent::__construct($context);
        $this->helperProduct = $helperProduct;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->config = $config;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->config->isEnabled()) {
            $page = $this->getRequest()->getParam('page', 1);
            $count = $this->getRequest()->getParam('count', 20);
            $collectionArray = $this->helperProduct->getCollectionJson($page, $count, true);
            $result->setData($collectionArray);
        } else {
            $result->setData([]);
        }
        return $result;
    }
}
