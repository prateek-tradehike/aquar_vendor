<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PaymentServicesPaypal\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\DataObject;
use Magento\PaymentServicesPaypal\Model\Config;
use Magento\PaymentServicesPaypal\Model\SdkService;
use Magento\PaymentServicesPaypal\Model\SdkService\PaymentOptionsBuilderFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Sdk Params Source
 */
class SdkParams extends DataObject implements SectionSourceInterface
{
    private const LOCATION = 'customer_data';

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
     * @param PaymentOptionsBuilderFactory $paymentOptionsBuilderFactory
     * @param SdkService $sdkService
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        PaymentOptionsBuilderFactory $paymentOptionsBuilderFactory,
        SdkService $sdkService,
        StoreManagerInterface $storeManager,
        Config $config,
        array $data = []
    ) {
        parent::__construct($data);
        $this->paymentOptionsBuilderFactory = $paymentOptionsBuilderFactory;
        $this->sdkService = $sdkService;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getSectionData()
    {
        return [
            'sdkParams' => $this->getSdkParams()
        ];
    }

    /**
     * Get script params for paypal smart buttons sdk.
     *
     * @return array
     */
    private function getSdkParams() : array
    {
        if (!$this->config->isEnabled()) {
            return [];
        }
        $storeViewId = $this->storeManager->getStore()->getId();
        $cachedParams = $this->sdkService->loadFromSdkParamsCache(self::LOCATION, $storeViewId);
        if (count($cachedParams) > 0) {
            return $cachedParams;
        }
        $paymentOptionsBuilder = $this->paymentOptionsBuilderFactory->create();
        $paymentOptionsBuilder->setAreButtonsEnabled(true);
        $paymentOptionsBuilder->setIsPayPalCreditEnabled($this->config->isFundingSourceEnabledByName('paypal_credit'));
        $paymentOptionsBuilder->setIsVenmoEnabled($this->config->isFundingSourceEnabledByName('venmo'));
        $paymentOptionsBuilder->setIsApplePayEnabled($this->config->isFundingSourceEnabledByName('applepay'));
        $paymentOptionsBuilder->setIsCreditCardEnabled(false);
        $paymentOptionsBuilder->setIsPaylaterMessageEnabled(true);
        $paymentOptions = $paymentOptionsBuilder->build();
        try {
            $params = $this->sdkService->getSdkParams(
                $paymentOptions,
                true,
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
