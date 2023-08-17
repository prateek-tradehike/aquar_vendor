<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaymentServicesBase\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Composer\ComposerInformation;
use Magento\ServicesConnector\Api\KeyValidationInterface;
use Magento\ServicesConnector\Exception\KeyNotFoundException;
use Magento\ServicesConnector\Exception\PrivateKeySignException;
use Magento\Store\Model\ScopeInterface;
use Magento\ServicesId\Model\ServicesConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config as AppConfig;

class Config
{
    private const EXTENSION_NAME = 'Magento_PaymentServicesBase';

    private const EXTENSION_PACKAGE_NAME = 'magento/payment-services';

    private const CONFIG_PATH_ENVIRONMENT = 'payment/payment_methods/method';

    private const CONFIG_PATH_MERCHANT_ID = 'payment/payment_methods/%s_merchant_id';

    private const VERSION_CACHE_KEY = 'payment-services-version';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ServicesConfig
     */
    private $servicesConfig;

    /**
     * @var KeyValidationInterface
     */
    private $keyValidator;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Extension version
     *
     * @var string
     */
    private $version;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var ComposerInformation
     */
    private $composerInformation;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ServicesConfig $servicesConfig
     * @param KeyValidationInterface $keyValidator
     * @param StoreManagerInterface $storeManager
     * @param CacheInterface $cache
     * @param ComposerInformation $composerInformation
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ServicesConfig $servicesConfig,
        KeyValidationInterface $keyValidator,
        StoreManagerInterface $storeManager,
        CacheInterface $cache,
        ComposerInformation $composerInformation
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->servicesConfig = $servicesConfig;
        $this->keyValidator = $keyValidator;
        $this->storeManager = $storeManager;
        $this->cache = $cache;
        $this->composerInformation = $composerInformation;
    }

    /**
     * Retrieve merchant ID from config
     *
     * @param string $environment
     * @return string
     */
    public function getMerchantId(string $environment = '') : string
    {
        $environment = $environment ?: $this->getEnvironmentType();
        return (string) $this->scopeConfig->getValue(
            $this->getMerchantIdConfigPath($environment),
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get SaaS environment ID
     *
     * @return null|string
     */
    public function getServicesEnvironmentId() : ?string
    {
        return $this->servicesConfig->getEnvironmentId();
    }

    /**
     * Get SaaS environment type
     *
     * @return string
     */
    public function getEnvironmentType() : string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH_ENVIRONMENT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get environment types for all the websites for report display
     *
     * @return string
     */
    public function getEnvironmentTypeAcrossWebsites() : string
    {
        $environment = 'sandbox';
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            $websiteEnvironment = (string) $this->scopeConfig->getValue(
                self::CONFIG_PATH_ENVIRONMENT,
                ScopeInterface::SCOPE_WEBSITE,
                $website->getId()
            );
            if ($websiteEnvironment === 'production') {
                $environment = $websiteEnvironment;
                break;
            }
        }
        return $environment;
    }

    /**
     * Get config path for merchant ID
     *
     * @param string $environment
     * @return string
     */
    private function getMerchantIdConfigPath(string $environment): string
    {
        return sprintf(self::CONFIG_PATH_MERCHANT_ID, $environment);
    }

    /**
     * Check is Magento Services configured.
     *
     * @param string $environment
     * @return bool
     */
    public function isMagentoServicesConfigured($environment = '') : bool
    {
        $environment = $environment ?: $this->getEnvironmentType();

        try {
            return $this->keyValidator->execute(self::EXTENSION_NAME, $environment) &&
                $this->servicesConfig->getEnvironmentId();
        } catch (KeyNotFoundException | PrivateKeySignException $exception) {
            return false;
        }
    }

    /**
     * Check if payments is enabled
     *
     * @return bool
     */
    public function isEnabled() : bool
    {
        return (bool) $this->scopeConfig->getValue(
            'payment/payment_services/active',
            ScopeInterface::SCOPE_STORE,
            null
        );
    }

    /**
     * Check if payments is configured
     *
     * @param string $environment
     * @return bool
     */
    public function isConfigured($environment = '') : bool
    {
        return $this->isMagentoServicesConfigured($environment)
            && $this->isEnabled()
            && $this->getMerchantId($environment);
    }

    /**
     * Get extension version
     *
     * @return string
     */
    public function getVersion(): string
    {
        $this->version = $this->version ?: $this->cache->load(self::VERSION_CACHE_KEY);
        if (!$this->version) {
            $installedPackages = $this->composerInformation->getInstalledMagentoPackages();
            $extensionVersion = $installedPackages[self::EXTENSION_PACKAGE_NAME]['version'] ?? null;
            if (!empty($extensionVersion)) {
                $this->version = $extensionVersion;
            } else {
                $this->version = 'UNKNOWN';
            }
            $this->cache->save($this->version, self::VERSION_CACHE_KEY, [AppConfig::CACHE_TAG]);
        }
        return $this->version;
    }
}
