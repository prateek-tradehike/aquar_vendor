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

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

class CategoryRegistry
{
    /**
     * Registry key list.
     */
    public const REGISTRY_KEY_CURRENT_CATEGORY = 'current_category';
    public const REGISTRY_KEY_CATEGORY = 'category';

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
            self::REGISTRY_KEY_CURRENT_CATEGORY,
            self::REGISTRY_KEY_CATEGORY,
        ]
    ) {
        $this->registry = $registry;
        $this->registryKeyMap = $registryKeyMap;
    }

    /**
     * Set Current Category
     *
     * @param CategoryInterface $category
     */
    public function setCurrentCategory(CategoryInterface $category): void
    {
        $this->unsetCurrentCategory();
        foreach ($this->registryKeyMap as $registryKey) {
            if (!$this->registry->registry($registryKey) instanceof CategoryInterface) {
                $this->registry->register($registryKey, $category);
            }
        }
    }

    /**
     * Get Current Category
     *
     * @return CategoryInterface
     * @throws LocalizedException
     */
    public function getCurrentCategory(): CategoryInterface
    {
        foreach ($this->registryKeyMap as $registryKey) {
            if ($this->registry->registry($registryKey) instanceof CategoryInterface) {
                return $this->registry->registry($registryKey);
            }
        }

        throw new LocalizedException(__("The current category isn't initialized"));
    }

    /**
     * Unset Current Cat
     *
     * @return void
     */
    public function unsetCurrentCategory(): void
    {
        foreach ($this->registryKeyMap as $registryKey) {
            $this->registry->unregister($registryKey);
        }
    }
}
