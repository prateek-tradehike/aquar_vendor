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

namespace Neklo\Core\Block\System\Contact;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Phrase;

class Link extends Field
{
    public const URL = 'https://store.neklo.com/contact/';

    public function render(AbstractElement $element): Phrase
    {
        $urlText = __('link');
        $urlTag = '<p style="display:inline"><a href="' . self::URL . '" target="_blank">' . $urlText . '</a></span>';

        return  __('Click on this %1 to contact us.', $urlTag);
    }
}
