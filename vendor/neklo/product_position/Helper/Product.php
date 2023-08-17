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

class Product extends Data
{
    const IMAGE_WIDTH = 100;

    const NO_SELECTION = 'no_selection';
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    private $stockFilter;

    /**
     * @var \Neklo\ProductPosition\Model\ResourceModel\Category
     */
    private $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    private $jsonMap = [
        'entity_id',
        'name',
        'sku',
        'price',
        'is_attached',
        'min_price',
        'max_price',
        'position',
    ];

    /**
     * Product constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\CatalogInventory\Helper\Stock $stockFilter
     * @param \Neklo\ProductPosition\Model\ResourceModel\Category $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        \Neklo\ProductPosition\Model\ResourceModel\Category $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->pricingHelper = $pricingHelper;
        $this->imageHelper = $imageHelper;
        $this->stockFilter = $stockFilter;
        $this->categoryFactory = $categoryFactory;
        $this->productCollection = $productCollectionFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getAttachedProductsJson()
    {
        $products = $this->categoryFactory->getAttachedProducts($this->getCategory());
        if (count($products) === 0) {
            return '{}';
        }
        return json_encode($products);
    }

    /**
     * @return string
     */
    public function getSortedProductsPositionJson()
    {
        $collection = $this->getProductCollection($this->getCategory()->getId())->setOrder('position', 'ASC');
        $products = [];
        foreach ($collection as $product) {
            $products[$product->getId()] = $product->getData();
        }

        if (count($products) === 0) {
            return '{}';
        }
        $productIdList = array_keys($products);
        $productPositionList = range(1, count($productIdList));
        $products = array_combine($productIdList, $productPositionList);
        return json_encode($products);
    }

    /**
     * @param int $page
     * @param int $count
     * @param bool $asArray
     *
     * @return array|string
     */
    public function getCollectionJson($page = 1, $count = 20, $asArray = false)
    {
        $productDataList = [];
        $collection = $this->getCollection($page, $count);
        $startPosition = $count * $page - $count + 1;
        foreach ($collection as $product) {
            $productData = $product->toArray($this->jsonMap);
            $productData['image'] = $this->resizeImage($product);
            if ((int)$productData['price'] == 0) {
                $minPrice = $this->pricingHelper->currencyByStore(
                    $productData['min_price'],
                    $this->getRequestStoreId(),
                    true,
                    false
                );
                $maxPrice = $this->pricingHelper->currencyByStore(
                    $productData['max_price'],
                    $this->getRequestStoreId(),
                    true,
                    false
                );
                $productData['price'] = "{$minPrice}-{$maxPrice}";
            } else {
                $productData['price'] = $this->pricingHelper->currencyByStore(
                    $productData['price'],
                    $this->getRequestStoreId(),
                    true,
                    false
                );
            }
            $productData['position'] = $startPosition;
            $productData['attached'] = (bool)$product['is_attached'];
            $productData['status'] = (bool)$product->getInventoryInStock() ? __('In Stock') : __('Out of Stock');
            $productDataList[] = $productData;
            $startPosition++;
        }
        if ($asArray) {
            return $productDataList;
        } else {
            return json_encode($productDataList);
        }
    }

    /**
     * @param int $page
     * @param int $count
     *
     * @return bool|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getCollection($page = 1, $count = 20)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->getProductCollection();
        $collection->setPageSize($count);
        if ($collection->getLastPageNumber() >= $page) {
            $collection->setCurPage($page);
            $collection->setOrder('position', 'ASC');
        } else {
            $collection->addFieldToFilter('entity_id', 0);
        }

        return $collection;
    }

    /**
     * @param null $categoryId
     * @param bool $stockFilter
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection($categoryId = null, $stockFilter = true)
    {
        if (null === $categoryId) {
            $categoryId = $this->getRequestCategoryId();
        }
        /** Add get default store view website id for correct sql query in product collection */
        $store = $this->getRequestStoreId();
        $store = $this->storeManager->getStore($store);
        if ($store->getWebsiteId() == 0) {
            $stores = $this->storeManager->getStores(false, true);
            $store = reset($stores);
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollection->create()
            ->addStoreFilter($store->getId())
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('thumbnail')
            ->joinField(
                "position",
                "catalog_category_product",
                "position",
                "product_id = entity_id",
                "category_id = {$categoryId}",
                "inner"
            )
            ->addAttributeToFilter(
                'visibility',
                [
                    'in' => [
                        \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                        \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                    ]
                ]
            )
            ->addAttributeToFilter(
                'status',
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
            )
            ->addPriceData(null, $store->getWebsiteId());

        if ($stockFilter) {
            $cond = [
                '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=1 AND {{table}}.is_in_stock=1',
                '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=0',
            ];
            $manageStock = $this->scopeConfig->getValue(
                \Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $showOutOfStock = $this->scopeConfig->getValue(
                \Magento\CatalogInventory\Model\Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $this->stockFilter->addIsInStockFilterToCollection($collection);
            if ($manageStock && !$showOutOfStock) {
                $cond[] = '{{table}}.use_config_manage_stock = 1 AND {{table}}.is_in_stock=1';
            } else {
                $cond[] = '{{table}}.use_config_manage_stock = 1';
            }
            $collection->joinField(
                'inventory_in_stock',
                'cataloginventory_stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '(' . join(') OR (', $cond) . ')'
            );
        }

        $collection->getSelect()
            ->joinLeft(
                [
                    'product_status' => $collection->getResource()
                        ->getTable(\Neklo\ProductPosition\Model\ResourceModel\Product\Status::TABLE_NAME)
                ],
                'e.entity_id = product_status.product_id AND product_status.category_id = ' . (int)$categoryId,
                [
                    'is_attached' => 'product_status.is_attached'
                ]
            );

        return $collection;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     */
    public function resizeImage($product)
    {
        $imageHelper = $this->imageHelper->init($product, 'product_thumbnail_image');
        $imageHelper
            ->constrainOnly(false)
            ->keepAspectRatio(false)
            ->keepFrame(false);
        $resizedImage = (string)$imageHelper->resize(self::IMAGE_WIDTH)->getUrl();

        return $resizedImage;
    }

    /**
     * @return int
     */
    public function getRequestCategoryId()
    {
        return (int)$this->_getRequest()->getParam('id', 0);
    }

    /**
     * @return int
     */
    public function getRequestStoreId()
    {
        return (int)$this->_getRequest()->getParam('store', 0);
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        $category = $this->coreRegistry->registry('current_category');
        return $category;
    }
}
