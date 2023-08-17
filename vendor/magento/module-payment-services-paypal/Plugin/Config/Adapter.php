<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaymentServicesPaypal\Plugin\Config;

use Magento\PaymentServicesPaypal\Model\HostedFieldsConfigProvider;
use Magento\PaymentServicesPaypal\Model\SmartButtonsConfigProvider;
use Magento\Payment\Model\Method\Adapter as PaymentAdapter;
use Magento\PaymentServicesPaypal\Model\Config;

class Adapter
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param Adapter $subject
     * @param $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsActive(PaymentAdapter $subject, $result): bool
    {
        if (in_array($subject->getCode(), [HostedFieldsConfigProvider::CODE, SmartButtonsConfigProvider::CODE])) {
            return $this->config->isEnabled();
        } else {
            return $result;
        }
    }
}
