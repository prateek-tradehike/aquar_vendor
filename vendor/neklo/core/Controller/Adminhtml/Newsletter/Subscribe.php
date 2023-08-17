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

namespace Neklo\Core\Controller\Adminhtml\Newsletter;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Neklo\Core\Helper\Sender;
use Laminas\Json\Json;

class Subscribe extends Action
{
    public const ADMIN_RESOURCE = 'Neklo_Core::config';

    /**
     * @var Sender
     */
    private Sender $sender;

    /**
     * @param Sender $sender
     * @param Context $context
     */
    public function __construct(
        Sender $sender,
        Context $context
    ) {
        parent::__construct($context);
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
            $this->sender->sendData($data);
        } catch (Exception $e) {
            $result['success'] = false;
            $this->getResponse()->setBody(Json::encode($result));
        }

        $this->getResponse()->setBody(Json::encode($result));
    }
}
