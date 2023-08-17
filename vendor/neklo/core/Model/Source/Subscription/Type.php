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

namespace Neklo\Core\Model\Source\Subscription;

use Magento\Framework\Data\OptionSourceInterface;

class Type implements OptionSourceInterface
{
    public const UPDATE_CODE = 'UPDATE';
    public const UPDATE_LABEL = 'My extensions updates';

    public const RELEASE_CODE = 'RELEASE';
    public const RELEASE_LABEL = 'New Releases';

    public const UPDATE_ALL_CODE = 'UPDATE_ALL';
    public const UPDATE_ALL_LABEL = 'All extensions updates';

    public const PROMO_CODE = 'PROMO';
    public const PROMO_LABEL = 'Promotions / Discounts';

    public const INFO_CODE = 'INFO';
    public const INFO_LABEL = 'Other information';

    /**
     * Configure Options Array
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::UPDATE_CODE,
                'label' => __('%1', self::UPDATE_LABEL),
            ],
            [
                'value' => self::RELEASE_CODE,
                'label' => __('%1', self::RELEASE_LABEL),
            ],
            [
                'value' => self::UPDATE_ALL_CODE,
                'label' => __('%1', self::UPDATE_ALL_LABEL),
            ],
            [
                'value' => self::PROMO_CODE,
                'label' => __('%1', self::PROMO_LABEL),
            ],
            [
                'value' => self::INFO_CODE,
                'label' => __('%1', self::INFO_LABEL),
            ],
        ];
    }
}
