<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaymentServicesPaypal\Model;

use Magento\PaymentServicesBase\Model\Config as BaseConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config
{
    private const CONFIG_PATH_FUNDING_FORMAT= 'payment/payment_services_paypal_smart_buttons/funding_%s';
    private const CONFIG_PATH_BUTTON_STYLE_FORMAT = 'payment/payment_services_paypal_smart_buttons/style_%s';
    private const BUTTON_STYLE_OPTIONS = [
        'layout' => 'string',
        'color' => 'string',
        'shape' => 'string',
        'height' => 'int',
        'label' => 'string',
        'tagline' => 'bool'
    ];
    private const BUTTON_STYLE_DEFAULT = [
        'height' => 'height_use_default'
    ];

    /**
     * @var BaseConfig
     */
    private $config;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param BaseConfig $config
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        BaseConfig $config,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get domain association config value
     *
     * @return string
     */
    public function getDomainAssociation(): string
    {
        return (string) $this->scopeConfig->getValue(
            'payment/payment_services_paypal_smart_buttons/domain_association',
            ScopeInterface::SCOPE_STORE,
        );
    }

    /**
     * Get soft descriptor config value
     *
     * @param string|null $storeCode
     * @return mixed
     */
    public function getSoftDescriptor($storeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/payment_services/soft_descriptor',
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    /**
     * Get 3DS config value
     *
     * @param string|null $storeCode
     * @return mixed
     */
    public function getThreeDS($storeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/payment_services_paypal_hosted_fields/three_ds',
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    /**
     * Get the merchant ID
     *
     * @param string $environment
     * @return string
     */
    public function getMerchantId($environment = '') : string
    {
        return $this->config->getMerchantId($environment);
    }

    /**
     * Check if the payment method is enabled
     *
     * @return bool
     */
    public function isEnabled() : bool
    {
        return $this->config->isEnabled();
    }

    /**
     * Check if Smart Buttons for a particular location is enabled
     *
     * @param string $location
     * @return bool
     */
    public function isLocationEnabled(string $location): bool
    {
        $storeCode = $this->storeManager->getStore()->getCode();
        return (bool) $this->scopeConfig->getValue(
            'payment/payment_services_paypal_smart_buttons/display_buttons_' . $location,
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    /**
     * Check if Hosted Fields method is enabled
     *
     * @return bool
     */
    public function isHostedFieldsEnabled(): bool
    {
        $storeCode = $this->storeManager->getStore()->getCode();
        return (bool) $this->scopeConfig->getValue(
            'payment/payment_services_paypal_hosted_fields/display_on_checkout',
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    /**
     * Check if the pay later message should be displayed
     *
     * @return bool
     */
    public function canDisplayPayLaterMessage() : bool
    {
        return (bool) $this->scopeConfig->getValue(
            'payment/payment_services_paypal_smart_buttons/display_paylater_message',
            ScopeInterface::SCOPE_STORE,
            null
        );
    }

    /**
     * Check if the vault is enabled
     *
     * @return bool
     */
    public function isVaultEnabled() : bool
    {
        return (bool) $this->scopeConfig->getValue(
            'payment/payment_services_paypal_vault/active',
            ScopeInterface::SCOPE_STORE,
            null
        );
    }

    /**
     * Get a funding source configuration by name (for the current store)
     *
     * @param string $funding
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isFundingSourceEnabledByName($funding = '') : bool
    {
        $storeCode = $this->storeManager->getStore()->getCode();
        $configVal = $this->scopeConfig->getValue(
            sprintf(self::CONFIG_PATH_FUNDING_FORMAT, $funding),
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );

        return $configVal === null ?: (bool) $configVal;
    }

    /**
     * Get configured button style options
     *
     * @return array
     */
    public function getButtonConfiguration() : array
    {
        $buttonConfig = [];
        $storeCode = $this->storeManager->getStore()->getCode();

        foreach (self::BUTTON_STYLE_OPTIONS as $styleName => $styleType) {
            // Check to see if we want to ignore custom values and use defaults from the provider.
            if (array_key_exists($styleName, self::BUTTON_STYLE_DEFAULT)) {
                $useDefaultParam = self::BUTTON_STYLE_DEFAULT[$styleName];
                $useDefault = (bool)$this->scopeConfig->getValue(
                    sprintf(self::CONFIG_PATH_BUTTON_STYLE_FORMAT, $useDefaultParam),
                    ScopeInterface::SCOPE_STORE,
                    $storeCode
                );

                if ($useDefault === true) {
                    continue;
                }
            }

            $styleVal = $this->scopeConfig->getValue(
                sprintf(self::CONFIG_PATH_BUTTON_STYLE_FORMAT, $styleName),
                ScopeInterface::SCOPE_STORE,
                $storeCode
            );

            if ($styleVal == null) {
                continue;
            } elseif ($styleType === 'bool') {
                $styleVal = (bool)$styleVal;
            } elseif ($styleType === 'int') {
                $styleVal = (int)$styleVal;
            }

            $buttonConfig[$styleName] = $styleVal;
        }

        return $buttonConfig;
    }
}
