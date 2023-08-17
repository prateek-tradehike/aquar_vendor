<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaymentServicesPaypal\Controller\Order;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\PaymentServicesPaypal\Model\OrderService;
use Magento\PaymentServicesBase\Model\HttpException;

class Create implements HttpPostActionInterface, CsrfAwareActionInterface
{
    private const VAULT_PARAM_KEY = 'vault';

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param OrderService $orderService
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        OrderService $orderService,
        ResultFactory $resultFactory,
        RequestInterface $request
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->orderService = $orderService;
        $this->resultFactory = $resultFactory;
        $this->request = $request;
    }

    /**
     * Dispatch the order creation request with Commerce params
     *
     * @return ResultInterface
     */
    public function execute() : ResultInterface
    {
        $shouldCardBeVaulted = $this->request->getParam(self::VAULT_PARAM_KEY) === 'true';
        $paymentSource = $this->request->getPost('payment_source');
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $quote = $this->checkoutSession->getQuote();
            $quoteId = $quote->getId();
            $isLoggedIn = $this->customerSession->isLoggedIn();
            $response = $this->orderService->create(
                [
                    'amount' => number_format($quote->getBaseGrandTotal(), 2, '.', ''),
                    'currency_code' => $quote->getCurrency()->getBaseCurrencyCode(),
                    'shipping_address' => $this->orderService->mapAddress($quote->getShippingAddress()),
                    'billing_address' => $this->orderService->mapAddress($quote->getBillingAddress()),
                    'payer' => $isLoggedIn
                        ? $this->orderService->buildPayer($quote, $this->customerSession->getCustomer()->getId())
                        : $this->orderService->buildGuestPayer($quote),
                    'is_digital' => $quote->isVirtual(),
                    'website_id' => $quote->getStore()->getWebsiteId(),
                    'payment_source' => $paymentSource,
                    'vault' => $shouldCardBeVaulted,
                    'quote_id' => $quoteId
                ]
            );
            $result->setHttpResponseCode($response['status'])
                ->setData(['response' => $response]);
        } catch (HttpException $e) {
            $result->setHttpResponseCode(500);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function createCsrfValidationException(RequestInterface $request) :? InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function validateForCsrf(RequestInterface $request) :? bool
    {
        return true;
    }
}
