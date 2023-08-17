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

namespace Neklo\ProductPosition\Block\Adminhtml\Category;

class Direction extends \Magento\Backend\Block\Template
{
    /**
     * @var array
     */
    private $dataTemplate;
    /**
     * @var \Neklo\ProductPosition\Helper\Data
     */
    private $helper;

    /**
     * Direction constructor.
     * @param \Neklo\ProductPosition\Helper\Data      $helper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array                                   $dataTemplate
     * @param array                                   $data
     */
    public function __construct(
        \Neklo\ProductPosition\Helper\Data $helper,
        \Magento\Backend\Block\Template\Context $context,
        array $dataTemplate = [],
        array $data = []
    ) {
        $this->dataTemplate = $dataTemplate;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function toHtml()
    {
        $template = $this->dataTemplate['ltr'];
        $this->setTemplate($template);
        return parent::toHtml();
    }
}
