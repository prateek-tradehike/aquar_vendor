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

namespace Neklo\Core\Block\System\Newsletter;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Neklo\Core\Block\System\Newsletter\Subscribe\Button;

class Subscribe extends Field
{
    /**
     * Render Element
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->setScope(false);
        $element->setCanUseWebsiteValue(false);
        $element->setCanUseDefaultValue(false);

        return parent::render($element);
    }

    /**
     * Get Element Html
     *
     * @param AbstractElement $element
     *
     * @return string
     * @throws LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $subscribeButton = $this->getLayout()->createBlock(Button::class, 'neklo_core_subscribe');
        $subscribeButton->setTemplate('Neklo_Core::system/subscribe/button.phtml');
        $subscribeButton->setContainerId($element->getContainer()->getHtmlId());

        return $subscribeButton->toHtml();
    }
}
