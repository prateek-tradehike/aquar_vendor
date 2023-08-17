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

namespace Neklo\ProductPosition\Plugin\Catalog\Controller\Adminhtml\Category;

class Save
{
    /**
     * @var \Neklo\ProductPosition\Helper\Parser
     */
    private $parser;

    /**
     * @var \Neklo\ProductPosition\Model\ResourceModel\Product\Status
     */
    private $statusResource;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    private $indexerFactory;

    /**
     * Save constructor.
     *
     * @param \Neklo\ProductPosition\Model\ResourceModel\Product\Status $statusResource
     * @param \Neklo\ProductPosition\Helper\Parser $parser
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Indexer\Model\IndexerFactory $indexerFactory
     */
    public function __construct(
        \Neklo\ProductPosition\Model\ResourceModel\Product\Status $statusResource,
        \Neklo\ProductPosition\Helper\Parser $parser,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory
    ) {
        $this->statusResource = $statusResource;
        $this->parser = $parser;
        $this->logger = $logger;
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Category\Save $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterExecute(\Magento\Catalog\Controller\Adminhtml\Category\Save $subject, $result)
    {
        $categoryId = $subject->getRequest()->getParam('entity_id');
        $attachJson = $subject->getRequest()->getParam('attached_category_products', []);
        $attachedProducts = is_array($attachJson) ? $attachJson : $this->parser->unserialize($attachJson);
        try {
            $this->statusResource->checkCategory($categoryId, $attachedProducts);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
        }
        $this->indexerFactory->create()->load('catalog_category_product')->reindexAll();
        return $result;
    }
}
