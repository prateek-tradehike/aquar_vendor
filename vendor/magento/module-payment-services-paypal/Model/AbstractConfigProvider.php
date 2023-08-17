<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaymentServicesPaypal\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\PaymentServicesPaypal\Model\SdkService\PaymentOptionsBuilderFactory;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractConfigProvider implements ConfigProviderInterface
{
    private const LOCATION = 'checkout';

    public const CODE = '';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PaymentOptionsBuilderFactory
     */
    private $paymentOptionsBuilderFactory;

    /**
     * @var SdkService
     */
    private $sdkService;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Config $config
     * @param PaymentOptionsBuilderFactory $paymentOptionsBuilderFactory
     * @param SdkService $sdkService
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        PaymentOptionsBuilderFactory $paymentOptionsBuilderFactory,
        SdkService $sdkService,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->paymentOptionsBuilderFactory = $paymentOptionsBuilderFactory;
        $this->sdkService = $sdkService;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'payment' => [
                $this->getCode() => []
            ]
        ];
    }

    /**
     * Get payment method code.
     *
     * @return string
     */
    protected function getCode() : string
    {
        return self::CODE;
    }

    /**
     * Get script params for PayPal js sdk loading.
     *
     * @return array
     */
    protected function getScriptParams() : array
    {
        $storeViewId = $this->storeManager->getStore()->getId();
        $cachedParams = $this->sdkService->loadFromSdkParamsCache(self::LOCATION, $storeViewId);
        if (count($cachedParams) > 0) {
            return $cachedParams;
        }
        $paymentOptionsBuilder = $this->paymentOptionsBuilderFactory->create();
        $paymentOptionsBuilder->setAreButtonsEnabled($this->config->isLocationEnabled('checkout'));
        $paymentOptionsBuilder->setIsPayPalCreditEnabled($this->config->isFundingSourceEnabledByName('paypal_credit'));
        $paymentOptionsBuilder->setIsVenmoEnabled($this->config->isFundingSourceEnabledByName('venmo'));
        $paymentOptionsBuilder->setIsApplePayEnabled($this->config->isFundingSourceEnabledByName('applepay'));
        $paymentOptionsBuilder->setIsCreditCardEnabled($this->config->isHostedFieldsEnabled());
        $paymentOptionsBuilder->setIsPaylaterMessageEnabled($this->config->canDisplayPayLaterMessage());
        $paymentOptions = $paymentOptionsBuilder->build();
        try {
            $params = $this->sdkService->getSdkParams(
                $paymentOptions,
                false,
                SdkService::PAYMENT_ACTION_AUTHORIZE
            );
        } catch (\InvalidArgumentException $e) {
            return [];
        }
        $result = [];
        foreach ($params as $param) {
            $result[] = [
                'name' => $param['name'],
                'value' => $param['value']
            ];
        }
        if (count($result) > 0) {
            $this->sdkService->updateSdkParamsCache($result, self::LOCATION, $storeViewId);
        }
        return $result;
    }
}
