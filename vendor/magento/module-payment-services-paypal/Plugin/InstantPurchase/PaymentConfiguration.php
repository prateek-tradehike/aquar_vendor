<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaymentServicesPaypal\Plugin\InstantPurchase;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\PaymentServicesBase\Model\HttpException;
use Magento\PaymentServicesPaypal\Model\OrderService;
use Magento\PaymentServicesPaypal\Model\Ui\TokenUiComponentProvider;
use Magento\Quote\Model\Quote;
use Magento\InstantPurchase\Model\QuoteManagement\PaymentConfiguration as InstantPurchasePaymentConfiguration;
use Magento\Framework\Exception\LocalizedException;
use Magento\PaymentServicesBase\Model\Config;

class PaymentConfiguration
{
    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param OrderService $orderService
     * @param CustomerSession $customerSession
     * @param Config $config
     */
    public function __construct(
        OrderService $orderService,
        CustomerSession $customerSession,
        Config $config
    ) {
        $this->orderService = $orderService;
        $this->customerSession = $customerSession;
        $this->config = $config;
    }

    /**
     * Create PayPal order on instant purchase.
     *
     * @param InstantPurchasePaymentConfiguration $subject
     * @param Quote $quote
     * @return Quote
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterConfigurePayment(
        InstantPurchasePaymentConfiguration $subject,
        Quote $quote
    ): Quote {
        $totalAmount = $quote->getBaseGrandTotal();
        $currencyCode = $quote->getCurrency()->getBaseCurrencyCode();
        $customer = $this->customerSession->getCustomer();
        $response = $this->orderService->create(
            [
                'amount' => number_format($totalAmount, 2, '.', ''),
                'currency_code' => $currencyCode,
                'is_digital' => $quote->getIsVirtual(),
                'website_id' => $quote->getStore()->getWebsiteId(),
                'shipping_address' => $this->orderService->mapAddress($quote->getShippingAddress()),
                'billing_address' => $this->orderService->mapAddress($quote->getBillingAddress()),
                'payer' => $this->orderService->buildPayer($quote, $customer->getId()),
                'payment_source' => TokenUiComponentProvider::CC_VAULT_SOURCE,
                'quote_id' => $quote->getId()
            ]
        );
        if (!$response['is_successful']) {
            throw new HttpException('Failed to create an order.');
        }

        $quote->getPayment()
            ->setAdditionalInformation('paypal_order_id', $response['paypal-order']['id'])
            ->setAdditionalInformation('payments_order_id', $response['paypal-order']['mp_order_id'])
            ->setAdditionalInformation('payments_mode', $this->config->getEnvironmentType());

        return $quote;
    }
}
