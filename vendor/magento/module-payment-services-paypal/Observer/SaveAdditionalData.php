<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaymentServicesPaypal\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\PaymentServicesBase\Model\Config;

class SaveAdditionalData extends AbstractDataAssignObserver
{
    private const PAYMENT_MODE_KEY = 'payments_mode';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string[]
     */
    private $additionalInformationList = [
        'payments_order_id',
        'paypal_order_id'
    ];

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        $paymentInfo = $this->readPaymentModelArgument($observer);
        $paymentInfo->setAdditionalInformation(
            self::PAYMENT_MODE_KEY,
            $this->config->getEnvironmentType()
        );
        if (!is_array($additionalData)) {
            return;
        }
        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}
