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

namespace Neklo\Core\Block\System\Newsletter\Subscribe;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Button as ButtonBlock;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

class Button extends Template
{
    /**
     * Get Button
     *
     * @return BlockInterface
     * @throws LocalizedException
     */
    public function getButton(): BlockInterface
    {
        $button = $this->getLayout()->createBlock(ButtonBlock::class);
        $button
            ->setType('button')
            ->setLabel(__('Subscribe'))
            ->setStyle("width:100%; box-sizing: border-box;")
            ->setId('neklo_core_subscribe');

        return $button;
    }

    /**
     * Get HTML
     *
     * @return string
     * @throws LocalizedException
     */
    public function getButtonHtml(): string
    {
        return $this->getButton()->toHtml();
    }
}
