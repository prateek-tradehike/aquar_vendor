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
use Neklo\Core\Block\System\Extension\ExtensionList;

class Extension extends Fieldset
{
    /**
     * Get Header Html
     *
     * @param AbstractElement $element
     *
     * @return string
     * @throws LocalizedException
     */
    protected function _getHeaderHtml($element): string
    {
        return parent::_getHeaderHtml($element) . $this->_getAfterHeaderHtml();
    }

    /**
     * Get After Header Html
     *
     * @return string
     * @throws LocalizedException
     */
    protected function _getAfterHeaderHtml(): string
    {
        $extensionListBlock = $this->getLayout()->createBlock(ExtensionList::class, 'neklo_core_extension_list');
        $extensionListBlock->setTemplate('Neklo_Core::system/extension/list.phtml');

        return $extensionListBlock->toHtml();
    }
}
