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

namespace Neklo\ProductPosition\Plugin\ImportExport\Model;

use \Magento\ImportExport\Model\Import as Model;
use \Magento\CatalogImportExport\Model\Import\Product;

class Import
{
    const CATALOG_PRODUCT = 'catalog_product';
    /**
     * @var \Neklo\ProductPosition\Model\ResourceModel\Product\Status
     */
    private $statusResource;
    /**
     * @var Product\CategoryProcessor
     */
    private $categoryProcessor;

    /**
     * Import constructor.
     *
     * @param \Neklo\ProductPosition\Model\ResourceModel\Product\Status $statusResource
     * @param Product\CategoryProcessor $categoryProcessor
     */
    public function __construct(
        \Neklo\ProductPosition\Model\ResourceModel\Product\Status $statusResource,
        \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor $categoryProcessor
    ) {
        $this->statusResource = $statusResource;
        $this->categoryProcessor = $categoryProcessor;
    }

    /**
     * @param Model $subject
     * @param $import
     *
     * @return mixed
     */
    public function afterImportSource(Model $subject, $import)
    {
        if ($subject->getEntity() == self::CATALOG_PRODUCT && $subject->getBehavior() == Model::BEHAVIOR_APPEND) {
            $source = $subject->getDataSourceModel();
            $separator = $subject->getData('_import_multiple_value_separator');
            $data = $source->getNextBunch();
            $categoryIds = [];
            $categories = [];
            foreach ($data as $iterator => $rowInfo) {
                $categoriesString = empty($rowInfo[Product::COL_CATEGORY]) ? '' : $rowInfo[Product::COL_CATEGORY];
                if (!empty($categoriesString)) {
                    $categories[] = $this->categoryProcessor->upsertCategories($categoriesString, $separator);
                }
            }

            if (!empty($categories)) {
                foreach ($categories as $iterator => $categoriesProduct) {
                    foreach ($categoriesProduct as $category) {
                        $categoryIds[] = $category;
                    }
                }
                $categoryIds = array_unique($categoryIds);
                foreach ($categoryIds as $categoryId) {
                    $this->statusResource->checkCategory($categoryId);
                }
            }
        }

        return $import;
    }
}
