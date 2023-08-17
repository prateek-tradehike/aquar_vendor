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

namespace Neklo\Core\Block\Adminhtml\System\Config\Form\Field\FieldArray;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\Factory;
use Neklo\Core\Model\Config\Backend\ArraySerialized\Email as EmailBackendModel;

class Email extends AbstractFieldArray
{
    /**
     * @var Factory
     */
    public Factory $elementFactory;

    /**
     * @param Context $context
     * @param Factory $elementFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    /**
     * Add Column
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->addColumn(
            EmailBackendModel::FIELD_EMAIL,
            [
                'label' => __('Email'),
                'class' => 'required-entry validate-email',
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Email');
        parent::_construct();
    }
}
