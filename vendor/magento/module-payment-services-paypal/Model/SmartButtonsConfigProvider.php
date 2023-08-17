<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaymentServicesPaypal\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\PaymentServicesPaypal\Model\SdkService\PaymentOptionsBuilderFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\PaymentServicesBase\Model\Config as BaseConfig;

class SmartButtonsConfigProvider extends AbstractConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'payment_services_paypal_smart_buttons';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var BaseConfig
     */
    private $baseConfig;

    /**
     * @var array
     */
    private $messageStyles;

    /**
     * @param Config $config
     * @param PaymentOptionsBuilderFactory $paymentOptionsBuilderFactory
     * @param SdkService $sdkService
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $url
     * @param BaseConfig $baseConfig
     * @param array $messageStyles
     */
    public function __construct(
        Config $config,
        PaymentOptionsBuilderFactory $paymentOptionsBuilderFactory,
        SdkService $sdkService,
        StoreManagerInterface $storeManager,
        UrlInterface $url,
        BaseConfig $baseConfig,
        array $messageStyles
    ) {
        $this->baseConfig = $baseConfig;
        $this->config = $config;
        $this->url = $url;
        $this->messageStyles = $messageStyles;
        parent::__construct($config, $paymentOptionsBuilderFactory, $sdkService, $storeManager);
    }

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        $config = parent::getConfig();
        if (!$this->baseConfig->isConfigured() || !$this->config->isLocationEnabled('checkout')) {
            $config['payment'][self::CODE]['isVisible'] = false;
            return $config;
        }
        $config['payment'][self::CODE]['isVisible'] = true;
        $config['payment'][self::CODE]['createOrderUrl'] = $this->url->getUrl('paymentservicespaypal/order/create');
        $config['payment'][self::CODE]['sdkParams'] = $this->getScriptParams();
        $config['payment'][self::CODE]['messageStyles'] = $this->messageStyles;
        $config['payment'][self::CODE]['canDisplayMessage'] = (bool) $this->config->canDisplayPayLaterMessage();
        $config['payment'][self::CODE]['buttonStyles'] = $this->config->getButtonConfiguration();
        return $config;
    }
}
