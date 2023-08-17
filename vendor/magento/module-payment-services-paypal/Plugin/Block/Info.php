<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaymentServicesPaypal\Plugin\Block;

use Magento\Payment\Block\Info as PaymentInfo;

class Info
{
    /**
     * @var string[]
     */
    private $fieldLabelMappings = [
        'paypal_order_id' => 'Payment Provider Reference',
        'payments_order_id' => 'Payment Order ID'
    ];

    /**
     * Manually change paypal_order_id display name
     *
     * @param PaymentInfo $info
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSpecificInformation(PaymentInfo $info, array $result): array
    {
        foreach ($result as $field => $value) {
            if (array_key_exists($field, $this->fieldLabelMappings)) {
                unset($result[$field]);
                $result[$this->fieldLabelMappings[$field]] = $value;
            }
        }
        return $result;
    }
}
