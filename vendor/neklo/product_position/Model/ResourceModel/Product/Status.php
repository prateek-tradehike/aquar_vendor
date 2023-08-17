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

namespace Neklo\ProductPosition\Model\ResourceModel\Product;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Status extends AbstractDb
{
    const TABLE_NAME = 'neklo_productposition_product_status';
    /**
     * @var \Neklo\ProductPosition\Helper\Product
     */
    private $productHelper;
    /**
     * @var Status\CollectionFactory
     */
    private $statusCollection;
    /**
     * @var \Neklo\ProductPosition\Model\Product\StatusFactory
     */
    private $statusFactory;

    /**
     * @var array
     */
    private $updatePositions = [];

    /**
     * Status constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Status\CollectionFactory $statusCollection
     * @param \Neklo\ProductPosition\Model\Product\StatusFactory $statusFactory
     * @param \Neklo\ProductPosition\Helper\Product $productHelper
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Neklo\ProductPosition\Model\ResourceModel\Product\Status\CollectionFactory $statusCollection,
        \Neklo\ProductPosition\Model\Product\StatusFactory $statusFactory,
        \Neklo\ProductPosition\Helper\Product $productHelper,
        $connectionName = null
    ) {
        $this->statusFactory = $statusFactory;
        $this->productHelper = $productHelper;
        $this->statusCollection = $statusCollection;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }

    /**
     * @param $categoryId
     * @param $attachedProduct
     */
    public function addCategoryAttached($categoryId, $attachedProduct)
    {
        if (is_array($attachedProduct)) {
            foreach ($attachedProduct as $productId => $attached) {
                $status = $this->statusFactory->create();
                $status->setData(
                    [
                        'product_id' => $productId,
                        'category_id' => $categoryId,
                        'is_attached' => $attached,
                    ]
                )->save();
            }
        }
    }

    /**
     * @param $categoryId
     * @param $attachedProductList
     *
     * @return $this
     */
    public function checkCategory($categoryId, $attachedProductList = null)
    {
        $newCategoryStatus = false;
        $categoryProducts = [];
        $statusCollection = $this->statusCollection->create()
            ->addFieldToFilter('category_id', ['eq' => $categoryId]);
        $products = $this->productHelper->getProductCollection($categoryId, false);
        if (!$attachedProductList) {
            if (!$statusCollection->getSize()) {
                foreach ($products as $product) {
                    $attachedProductList[$product->getId()] = (int)$product->getIsAttached();
                }
                $this->addCategoryAttached($categoryId, $attachedProductList);
                $newCategoryStatus = true;
            } else {
                foreach ($statusCollection as $status) {
                    $attachedProductList[$status->getProductId()] = (int)$status->getIsAttached();
                }
            }
        } elseif (!$statusCollection->getSize()) {
            $this->addCategoryAttached($categoryId, $attachedProductList);
        }
        foreach ($products as $product) {
            $categoryProducts[$product->getId()] = (int)$product->getIsAttached();
        }
        $newCategoryProducts = array_diff_key($categoryProducts, $attachedProductList);
        if ((count($categoryProducts) !== count($attachedProductList)) || $newCategoryStatus) {
            $this->checkRemovedProductsFromCategory($categoryId, $categoryProducts);
            $this->sortCategory($categoryId, $newCategoryProducts);
        }
        $statusCollection->resetData()->load();
        foreach ($statusCollection as $status) {
            $attachedProduct = $attachedProductList[$status->getProductId()]
                ? $attachedProductList[$status->getProductId()] : false;
            if ((int)$status->getIsAttached() !== (int)$attachedProduct) {
                $status->setIsAttached($attachedProductList[$status->getProductId()]);
                $status->save();
            }
        }

        return $this;
    }

    /**
     * @param $productId
     * @param null $categories
     *
     * @return $this
     */
    public function removeProductFromCategory($productId, $categories = null)
    {
        if ($categories) {
            $statusCollection = $this->statusCollection->create()
                ->addFieldToFilter('product_id', ['eq' => $productId])
                ->addFieldToFilter('category_id', ['nin' => [$categories]]);
        } else {
            $statusCollection = $this->statusCollection->create()
                ->addFieldToFilter('product_id', ['eq' => $productId]);
        }
        foreach ($statusCollection as $status) {
            $status->delete();
        }

        return $this;
    }
    /**
     * @param $categoryId
     * @param $productId
     *
     * @return $this
     */
    public function checkStatusProduct($categoryId, $productId)
    {
        $products = $this->productHelper->getProductCollection($categoryId, false)
            ->setOrder('position', 'ASC')
            ->getData();
        $position = $this->statusCollection->create()
            ->addFieldToFilter('product_id', ['eq' => $productId])
            ->addFieldToFilter('category_id', ['eq' => $categoryId]);
        if ($position->getSize()) {
            $this->checkProductPosition($products, $categoryId, $productId);
            return $this;
        }
        $busyPosition = [];
        $this->addCategoryAttached($categoryId, [$productId => 0]);
        $sortedProducts = [];
        foreach ($products as $iterator => $product) {
            if (!$product['position']) {
                $sortedProducts[$product['entity_id']] = 1;
                continue;
            }
            if ($product['is_attached']) {
                $busyPosition[] = (int)$product['position'];
                unset($products[$iterator]);
                continue;
            }
            $sortedProducts[$product['entity_id']] = (int)$product['position'];
        }
        $this->sortProducts($sortedProducts, $busyPosition)->savePositionProduct($categoryId);

        return $this;
    }

    /**
     * @param $products
     * @param $categoryId
     * @param $productId
     */
    private function checkProductPosition($products, $categoryId, $productId)
    {
        $neededSort = false;
        foreach ($products as $iterator => $product) {
            $currentPosition = (int)$product['position'];
            if ($product['entity_id'] == $productId) {
                if ($currentPosition == 0) {
                    $this->sortCategory($categoryId);
                    break;
                }
                $nextProduct = $products[$iterator + 1] ? $products[$iterator + 1] : false;
                $nextPosition = (int)$nextProduct['position'];
                $prevProduct = $iterator > 0 ? $products[$iterator - 1] : false;
                $prevPosition = (int)$prevProduct['position'];
                if ($nextPosition == $currentPosition || $prevPosition == $currentPosition) {
                    $this->updatePositions[$product['entity_id']] = $product['position'] + 1;
                    $neededSort = true;
                }
                continue;
            }

            if ($neededSort) {
                $lastPosition = end($this->updatePositions);
                if ($lastPosition > $currentPosition) {
                    continue;
                }
                $this->updatePositions[$product['entity_id']] = $lastPosition + 1;
            }
        }
        $this->savePositionProduct($categoryId);
    }

    /**
     * @param $categoryId
     *
     * @return $this
     */
    private function savePositionProduct($categoryId)
    {
        $connection = $this->getConnection();
        if (!empty($this->updatePositions)) {
            foreach ($this->updatePositions as $productId => $position) {
                $connection->update(
                    $this->getTable('catalog_category_product'),
                    ['position' => $position],
                    'product_id=' . $productId . ' AND category_id=' . $categoryId
                );
            }
            $this->updatePositions = [];
        }

        return $this;
    }

    /**
     * Sorted category position, if isset new products, add in table product_status
     * @param $categoryId
     * @param $newCategoryProducts
     */
    private function sortCategory($categoryId, $newCategoryProducts = [])
    {
        if (!empty($newCategoryProducts)) {
            $this->addCategoryAttached($categoryId, $newCategoryProducts);
        }

        $products = $this->productHelper->getProductCollection($categoryId, false)
            ->setOrder('position', 'ASC')
            ->getData();
        $busyPosition = [];
        $sortedProducts = [];
        $i = 1;
        foreach ($products as $product) {
            if ((int)$product['is_attached'] == 1) {
                $busyPosition[] = (int)$product['position'];
                $i++;
                continue;
            }
            $sortedProducts[$product['entity_id']] = $i;
            $i++;
        }
        $this->sortProducts($sortedProducts, $busyPosition)->savePositionProduct($categoryId);
    }

    /**
     * @param $products
     * @param $busyPosition
     *
     * @return $this
     */
    private function sortProducts($products, $busyPosition)
    {
        foreach ($products as $productId => $currentPosition) {
            $newPosition = $this->findPositionProduct($currentPosition, $busyPosition);
            $this->updatePositions[$productId] = $newPosition;
            $busyPosition[] = $newPosition;
        }

        return $this;
    }

    /**
     * @param $position
     * @param $busyPosition
     *
     * @return int
     */
    private function findPositionProduct($position, $busyPosition)
    {
        $attachedPosition= array_search($position, $busyPosition);
        if ($attachedPosition !== false) {
            $position++;
            $position = $this->findPositionProduct($position, $busyPosition);
        }

        return $position;
    }

    /**
     * @param $categoryId
     * @param $categoryProducts
     */
    private function checkRemovedProductsFromCategory($categoryId, $categoryProducts)
    {
        $statusCollection = $this->statusCollection->create()
            ->addFieldToFilter('category_id', ['eq' => $categoryId]);
        foreach ($statusCollection as $status) {
            $checkProduct = array_key_exists($status->getProductId(), $categoryProducts);
            if ($checkProduct === false) {
                $status->delete();
            }
        }
    }
}
