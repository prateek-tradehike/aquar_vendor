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

namespace Neklo\Core\Controller\Adminhtml\Contact;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\UrlInterface;
use Neklo\Core\Helper\Sender;
use Laminas\Json\Json;

class Send extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Neklo_Core::config';

    /**
     * @var ProductMetadata
     */
    private ProductMetadata $metadata;

    /**
     * @var Sender
     */
    private Sender $sender;

    /**
     * @param Context $context
     * @param ProductMetadata $metadata
     * @param Sender $sender
     */
    public function __construct(
        Context $context,
        ProductMetadata $metadata,
        Sender $sender
    ) {
        parent::__construct($context);
        $this->metadata = $metadata;
        $this->sender = $sender;
    }

    /**
     * Execute
     *
     * @return void
     */
    public function execute(): void
    {
        $result['success'] = true;
        try {
            $data = $this->getRequest()->getPost();
            $data['version'] = $this->metadata->getVersion();
            $data['url'] = $this->_url->getBaseUrl(UrlInterface::URL_TYPE_WEB);
            $data['id'] = '<order_item_customer></order_item_customer>';
            $this->sender->sendData($data);
        } catch (Exception $e) {
            $result['success'] = false;
        }

        $this->getResponse()->setBody(Json::encode($result));
    }
}
