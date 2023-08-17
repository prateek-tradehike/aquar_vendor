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

namespace Neklo\Core\Model\Source;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Neklo\Core\Helper\Extension;

class Reason implements OptionSourceInterface
{
    /**
     * @var Extension
     */
    private Extension $extensionHelper;

    /**
     * @var ProductMetadataInterface
     */
    private ProductMetadataInterface $metadata;

    /**
     * @param Extension $extensionHelper
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        Extension $extensionHelper,
        ProductMetadataInterface $productMetadata
    ) {
        $this->extensionHelper = $extensionHelper;
        $this->metadata = $productMetadata;
    }

    /**
     * Configure Options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $reasonList = [];
        $reasonList[] = [
            'value' => '',
            'label' => __('Please select')
        ];

        $reasonList[] = [
            'value' => 'Magento v' . $this->metadata->getVersion(),
            'label' => __('Magento Related Support')
        ];

        $reasonList[] = [
            'value' => 'New Extension',
            'label' => __('Request New Extension Development')
        ];

        $moduleList = $this->extensionHelper->getModuleList();
        foreach ($moduleList as $moduleCode => $moduleData) {
            $moduleTitle = $moduleData['name'] . ' v' . $moduleData['version'];
            $reasonList[] = [
                'value' => $moduleCode . ' ' . $moduleData['version'],
                'label' => __(sprintf('%s Support', $moduleTitle))
            ];
        }

        $reasonList[] = ['value' => 'other', 'label' => __('Other Reason')];

        return $reasonList;
    }
}
