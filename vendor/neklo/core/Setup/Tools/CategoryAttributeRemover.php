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

namespace Neklo\Core\Setup\Tools;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\SetupInterface;

class CategoryAttributeRemover
{
    /**
     * @var CategorySetupFactory
     */
    private CategorySetupFactory $categorySetupFactory;

    /**
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Remove Attribute
     *
     * @param SetupInterface $setup
     * @param string $attributeCode
     *
     * @return void
     */
    public function remove(SetupInterface $setup, string $attributeCode): void
    {
        $this->getCategorySetup($setup)->removeAttribute(Category::ENTITY, $attributeCode);
    }

    /**
     * Get Category Setup
     *
     * @param SetupInterface $setup
     *
     * @return CategorySetup
     */
    private function getCategorySetup(SetupInterface $setup): CategorySetup
    {
        return $this->categorySetupFactory->create(['setup' => $setup]);
    }
}
