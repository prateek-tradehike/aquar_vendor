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

namespace Neklo\ProductPosition\Plugin\Catalog\Controller\Adminhtml\Product;

class Save
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Neklo\ProductPosition\Model\ResourceModel\Product\Status
     */
    private $statusResource;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;


    private $indexerFactory;

    /**
     * Save constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Neklo\ProductPosition\Model\ResourceModel\Product\Status $statusResource
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Indexer\Model\IndexerFactory $indexerFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Neklo\ProductPosition\Model\ResourceModel\Product\Status $statusResource,
        \Magento\Framework\Registry $registry,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory
    ) {
        $this->registry = $registry;
        $this->logger = $logger;
        $this->statusResource = $statusResource;
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Save $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterExecute(\Magento\Catalog\Controller\Adminhtml\Product\Save $subject, $result)
    {
        $product = $subject->getRequest()->getParam('product');
        $productId = $subject->getRequest()->getParam('id');
        if (!$productId) {
            $productId = $this->registry->registry('current_product')->getId();
        }
        $categories = isset($product['category_ids']) ? $product['category_ids'] : null;

        if (!array_key_exists('is_in_stock', $product['quantity_and_stock_status'])
            || !$product['quantity_and_stock_status']['is_in_stock']
            || $product['status'] == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
        ) {
            $this->statusResource->removeProductFromCategory($productId, $categories);
            return $result;
        }

        if (is_array($categories) && !empty($categories)) {
            try {
                foreach ($categories as $categoryId) {
                    $this->statusResource->checkStatusProduct($categoryId, $productId);
                }
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage(), ['exception' => $e]);
            }
        }
        $this->statusResource->removeProductFromCategory($productId, $categories);
        $this->indexerFactory->create()->load('cataloginventory_stock')->reindexAll();
        $this->indexerFactory->create()->load('catalog_category_product')->reindexAll();

        return $result;
    }
}
