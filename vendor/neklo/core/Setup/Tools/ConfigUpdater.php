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

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\Setup\SetupInterface;

class ConfigUpdater
{
    /**
     * Default Scope ID value.
     */
    public const SCOPE_ID_DEFAULT = 0;

    /**
     * @var ScopeConfig
     */
    private ScopeConfig $scopeConfig;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param ScopeConfig $scopeConfig
     * @param Config $config
     */
    public function __construct(
        ScopeConfig $scopeConfig,
        Config $config
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
    }

    /**
     * Update config if empty
     *
     * @param SetupInterface $setup
     * @param string $path
     * @param string|null $value
     * @param string $scope
     * @param int $scopeId
     */
    public function updateIfEmpty(
        SetupInterface $setup,
        string         $path,
        string         $value = null,
        string         $scope = ScopeConfig::SCOPE_TYPE_DEFAULT,
        int            $scopeId = self::SCOPE_ID_DEFAULT
    ): void {
        if (!$this->scopeConfig->getValue($path, $scope, $scopeId)) {
            $this->update($setup, $path, $value, $scope, $scopeId);
        }
    }

    /**
     * Update config
     *
     * @param SetupInterface $setup
     * @param string $path
     * @param string|null $value
     * @param string $scope
     * @param int $scopeId
     */
    public function update(
        SetupInterface $setup,
        string         $path,
        string         $value = null,
        string         $scope = ScopeConfig::SCOPE_TYPE_DEFAULT,
        int            $scopeId = self::SCOPE_ID_DEFAULT
    ): void {
        if ($value === null) {
            $this->delete($setup, $path, $scope, $scopeId);
        } else {
            $this->config->saveConfig($path, $value, $scope, $scopeId);
        }
    }

    /**
     * Delete config
     *
     * @param SetupInterface $setup
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     */
    public function delete(
        SetupInterface $setup,
        string         $path,
        string         $scope = ScopeConfig::SCOPE_TYPE_DEFAULT,
        int            $scopeId = self::SCOPE_ID_DEFAULT
    ): void {
        $this->config->deleteConfig($path, $scope, $scopeId);
    }
}
