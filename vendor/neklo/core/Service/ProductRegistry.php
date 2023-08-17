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
declare(strict_types=1);

namespace Neklo\Core\Service;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

class ProductRegistry
{
    /**
     * Registry key list.
     */
    public const REGISTRY_KEY_CURRENT_PRODUCT = 'current_product';
    public const REGISTRY_KEY_PRODUCT = 'product';

    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var string[]
     */
    private array $registryKeyMap;

    /**
     * @param Registry $registry
     * @param array $registryKeyMap
     */
    public function __construct(
        Registry $registry,
        array $registryKeyMap = [
            self::REGISTRY_KEY_CURRENT_PRODUCT,
            self::REGISTRY_KEY_PRODUCT,
        ]
    ) {
        $this->registry = $registry;
        $this->registryKeyMap = $registryKeyMap;
    }

    /**
     * Set Current Product
     *
     * @param ProductInterface $product
     * @return void
     */
    public function setCurrentProduct(ProductInterface $product): void
    {
        $this->unsetCurrentProduct();
        foreach ($this->registryKeyMap as $registryKey) {
            if (!$this->registry->registry($registryKey) instanceof ProductInterface) {
                $this->registry->register($registryKey, $product);
            }
        }
    }

    /**
     * Get Current Product
     *
     * @return ProductInterface
     * @throws LocalizedException
     */
    public function getCurrentProduct(): ProductInterface
    {
        foreach ($this->registryKeyMap as $registryKey) {
            if ($this->registry->registry($registryKey) instanceof ProductInterface) {
                return $this->registry->registry($registryKey);
            }
        }

        throw new LocalizedException(__("The current product isn't initialized."));
    }

    /**
     * Unset Current Product
     *
     * @return void
     */
    public function unsetCurrentProduct(): void
    {
        foreach ($this->registryKeyMap as $registryKey) {
            $this->registry->unregister($registryKey);
        }
    }
}
