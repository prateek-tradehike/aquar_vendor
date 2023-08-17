<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaymentServicesPaypal\Plugin\Config;

use Magento\Config\Model\Config\Structure as ConfigStructure;

class Structure
{
    /**
     * @var string[]
     */
    private $sections = [
        'payment_us_recommended_solutions_magento_payments_hosted_fields',
        'payment_us_recommended_solutions_magento_payments_smart_buttons',
    ];

    /**
     * @param ConfigStructure $subject
     * @param array $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionList(ConfigStructure $subject, array $result) : array
    {
        foreach ($this->sections as $section) {
            $result[$section] = true;
        }
        return $result;
    }
}
