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

namespace Neklo\ProductPosition\Model\ResourceModel;

class Category extends \Magento\Catalog\Model\ResourceModel\Category
{
    /**
     * @param $collection
     *
     * @return array
     */
    public function getSortedProductsPosition($collection)
    {
        $select = $collection->getSelect()
            ->order(
                [
                    'at_position.position ' . \Magento\Framework\DB\Select::SQL_ASC,
                ]
            );

        $select->reset(\Magento\Framework\DB\Select::COLUMNS)->columns([
            'entity_id',
            'at_position.position'
        ]);
        $positions = $this->getConnection()->fetchAssoc($select);
        $sortedPosition = $positions;
        return $sortedPosition;
    }

    /**
     * Get attached products for category products
     *
     * @param \Magento\Catalog\Model\Category $category
     *
     * @return array
     */
    public function getAttachedProducts($category)
    {
        $select = $this->getConnection()->select()
            ->from(
                ['category_product' => $this->getCategoryProductTable()],
                []
            )
            ->joinLeft(
                ['product_status' => $this->_resource->getTableName(
                    \Neklo\ProductPosition\Model\ResourceModel\Product\Status::TABLE_NAME
                )
                ],
                'category_product.product_id = product_status.product_id AND product_status.category_id = ' . (int)$category->getId(),
                []
            )
            ->where('category_product.category_id = ' . (int)$category->getId())
            ->columns(
                [
                    'product_id'  => 'category_product.product_id',
                    'is_attached' => 'IF(product_status.is_attached, product_status.is_attached, 0)',
                ]
            );
        $attachedProductList = $this->getConnection()->fetchPairs($select);
        return $attachedProductList;
    }
}
