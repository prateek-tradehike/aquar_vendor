<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaymentServicesPaypal\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\PaymentServicesBase\Model\HttpException;
use Magento\PaymentServicesPaypal\Model\OrderService;
use Magento\Quote\Model\Quote\Address as Address;
use Magento\ServiceProxy\Controller\Adminhtml\AbstractProxyController;

class Create extends AbstractProxyController implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_ServiceProxy::services';

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var QuoteSession
     */
    private $quoteSession;

    /**
     * @param Context $context
     * @param QuoteSession $quoteSession
     * @param OrderService $orderService
     */
    public function __construct(
        Context $context,
        QuoteSession $quoteSession,
        OrderService $orderService
    ) {
        parent::__construct($context);
        $this->quoteSession = $quoteSession;
        $this->orderService = $orderService;
    }

    /**
     * @inheritDoc
     */
    public function execute(): ResultInterface
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $quote = $this->quoteSession->getQuote();
            $customerId = $quote->getCustomerId();
            $payer = $customerId !== null && $customerId != ""
                ? $this->orderService->buildPayer($quote, $customerId)
                : $this->orderService->buildGuestPayer($quote);
            $paymentSource = $this->getRequest()->getPost('payment_source');
            $response = $this->orderService->create(
                [
                    'amount' => number_format($quote->getBaseGrandTotal(), 2, '.', ''),
                    'currency_code' => $quote->getCurrency()->getBaseCurrencyCode(),
                    'shipping_address' => $this->mapAddress($quote->getShippingAddress()),
                    'billing_address' => $this->mapAddress($quote->getBillingAddress()),
                    'payer' => $payer,
                    'is_digital' => $quote->isVirtual(),
                    'website_id' => $quote->getStore()->getWebsiteId(),
                    'store_code' => $quote->getStore()->getCode(),
                    'payment_source' => $paymentSource
                ]
            );
            $result->setHttpResponseCode($response['status'])
                ->setData(['response' => $response]);

        } catch (HttpException $e) {
            $result->setHttpResponseCode(500);
            $result->setData($e->getMessage());
        }
        return $result;
    }

    /**
     * Map Commerce address fields to DTO
     *
     * @param Address $address
     * @return array|null
     */
    private function mapAddress(Address $address) :? array
    {
        return [
            'full_name' => $address->getFirstname() . ' ' . $address->getLastname(),
            'address_line_1' => $address->getStreet()[0],
            'address_line_2' => $address->getStreet()[1] ?? null,
            'admin_area_1' => $address->getRegion(),
            'admin_area_2' => $address->getCity(),
            'postal_code' => $address->getPostcode(),
            'country_code' => $address->getCountry()
        ];
    }
}
