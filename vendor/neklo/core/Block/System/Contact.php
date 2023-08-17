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

namespace Neklo\Core\Block\System;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Neklo\Core\Block\System\Contact\Header;

class Contact extends Fieldset
{
    /**
     * Return header html
     *
     * @param AbstractElement $element
     * @return string
     * @throws LocalizedException
     */
    protected function _getHeaderHtml($element): string
    {
        return parent::_getHeaderHtml($element) . $this->_getAfterHeaderHtml();
    }

    /**
     * Return after html
     *
     * @return string
     * @throws LocalizedException
     */
    protected function _getAfterHeaderHtml(): string
    {
        $subscribeButton = $this->getLayout()->createBlock(Header::class, 'neklo_core_contact_header');
        $subscribeButton->setTemplate('Neklo_Core::system/contact/header.phtml');

        return $subscribeButton->toHtml();
    }
}
