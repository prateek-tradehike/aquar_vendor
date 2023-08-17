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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\SetupInterface;

class ConfigMover
{
    /**
     * Configuration table name.
     */
    public const TABLE_NAME = 'core_config_data';

    /**
     * Configuration path field name.
     */
    public const FIELD_PATH = 'path';

    /**
     * Configuration path delimiter.
     */
    public const PATH_DELIMITER = '/';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Move config
     *
     * @param SetupInterface $setup
     * @param string $sourcePath
     * @param string $destinationPath
     *
     * @return void
     */
    public function move(SetupInterface $setup, string $sourcePath, string $destinationPath): void
    {
        $value = $this->scopeConfig->getValue($sourcePath);
        if (is_array($value)) {
            foreach (array_keys($value) as $key) {
                $this->move(
                    $setup,
                    $sourcePath . self::PATH_DELIMITER . $key,
                    $destinationPath . self::PATH_DELIMITER . $key
                );
            }
        } else {
            $connection = $setup->getConnection();
            $connection->update(
                $setup->getTable(self::TABLE_NAME),
                [self::FIELD_PATH => $destinationPath],
                sprintf('%s = %s', self::FIELD_PATH, $connection->quote($sourcePath))
            );
        }
    }
}
