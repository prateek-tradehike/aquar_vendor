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

namespace Neklo\Core\Helper;

use Magento\Backend\App\ConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Config extends AbstractHelper
{
    public const NOTIFICATION_TYPE = 'neklo_core/notification/type';

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $backendConfig;

    /**
     * @param Context $context
     * @param ConfigInterface $backendConfig
     */
    public function __construct(
        Context $context,
        ConfigInterface $backendConfig
    ) {
        parent::__construct($context);
        $this->backendConfig = $backendConfig;
    }

    /**
     * Get Notification Type List
     *
     * @return array
     */
    public function getNotificationTypeList(): array
    {
        return explode(',', $this->backendConfig->getValue(self::NOTIFICATION_TYPE));
    }
}
