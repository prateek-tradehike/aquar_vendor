<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaymentServicesPaypal\Plugin;

use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\View;

class OrderViewEnvironmentMessage
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param ManagerInterface $messageManager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ManagerInterface $messageManager,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->messageManager = $messageManager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Check the environment type, if it is from the sandbox environment then display a warning message.
     *
     * @param View $subject
     */
    public function beforeExecute(View $subject)
    {
        $id = $subject->getRequest()->getParam('order_id');
        $warningMessage = 'This order was processed with a
        Payment Services sandbox payment method. No money was collected.';

        if ($id === null) {
            return;
        }
        try {
            $order = $this->orderRepository->get($id);
        } catch (\Exception $e) {
            return;
        }

        if ($order->getPayment()->getAdditionalInformation('payments_mode') === 'sandbox') {
            $this->messageManager->addWarningMessage(__($warningMessage));
        }
    }
}
