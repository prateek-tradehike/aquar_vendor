<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaymentServicesPaypal\Model;

use Magento\PaymentServicesBase\Model\ServiceClientInterface;
use Magento\Framework\App\Request\Http;
use Magento\PaymentServicesBase\Model\HttpException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as Address;
use Magento\Customer\Model\Customer;

class OrderService
{
    /**
     * @var ServiceClientInterface
     */
    private $httpClient;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ServiceClientInterface $httpClient
     * @param Config $config
     */
    public function __construct(
        ServiceClientInterface $httpClient,
        Config $config
    ) {
        $this->httpClient = $httpClient;
        $this->config = $config;
    }

    /**
     * Map DTO fields and send the order creation request to the backend service
     *
     * @param array $data
     * @return array
     * @throws HttpException
     */
    public function create(array $data) : array
    {
        $order = [
            'paypal-order' => [
                'amount' => [
                    'currency_code' => $data['currency_code'],
                    'value' => $data['amount'] ?? 0.00
                ],
                'is_digital' => !!$data['is_digital'] ?? false,
                'website_id' => $data['website_id'],
                'payment_source' => $data['payment_source'] ?? '',
                'vault' => $data['vault'] ?? false
            ]
        ];
        $order['paypal-order']['shipping-address'] = $data['shipping_address'] ?? null;
        $order['paypal-order']['billing-address'] = $data['billing_address'] ?? null;
        $order['paypal-order']['payer'] = $data['payer'] ?? null;
        $softDescriptor = $this->config->getSoftDescriptor($data['store_code'] ?? null);
        if ($softDescriptor) {
            $order['paypal-order']['soft_descriptor'] = $softDescriptor;
        }
        $headers = [
            'Content-Type' => 'application/json',
            'x-scope-id' => $data['website_id']
        ];
        if (isset($data['vault']) && $data['vault']) {
            $headers['x-commerce-customer-id'] = $data['payer']['customer_id'];
        }
        if (isset($data['quote_id']) && $data['quote_id']) {
            $headers['x-commerce-quote-id'] = $data['quote_id'];
        }
        $response = $this->httpClient->request(
            $headers,
            '/payments/' . $this->config->getMerchantId() . '/payment/paypal/order',
            Http::METHOD_POST,
            json_encode($order)
        );

        return $response;
    }

    /**
     * Update the PayPal order with selective params
     *
     * @param string $id
     * @param array $data
     * @throws HttpException
     */
    public function update(string $id, array $data) : void
    {
        $order = [
            'paypal-order-update' => [
                'reference_id' => 'default',
                'amount' => [
                    'operation' => 'REPLACE',
                    'value' => [
                        'currency_code' => $data['currency_code'],
                        'value' => $data['amount']
                    ]
                ]
            ]
        ];
        $response = $this->httpClient->request(
            ['Content-Type' => 'application/json'],
            '/payments/' . $this->config->getMerchantId() . '/payment/paypal/order/' . $id,
            Http::METHOD_PATCH,
            json_encode($order)
        );
        if (!$response['is_successful']) {
            throw new HttpException('Failed to update an order.');
        }
    }

    /**
     * Get the Order object from PayPal
     *
     * @param string $id
     * @return array
     * @throws HttpException
     */
    public function get(string $id) : array
    {
        $response = $this->httpClient->request(
            ['Content-Type' => 'application/json'],
            '/payments/' . $this->config->getMerchantId() . '/payment/paypal/order/' . $id,
            Http::METHOD_GET,
        );
        if (!$response['is_successful']) {
            throw new HttpException('Failed to retrieve an order.');
        }
        return $response;
    }

    /**
     * Map Commerce address fields to DTO
     *
     * @param Address $address
     * @return array|null
     */
    public function mapAddress(Address $address) :? array
    {
        if ($address->getCountry() === null) {
            return null;
        }
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

    /**
     * Build the Payer object for PayPal order creation
     *
     * @param Quote $quote
     * @param String $customerId
     * @return array
     */
    public function buildPayer(Quote $quote, String $customerId) : array
    {
        $billingAddress = $quote->getBillingAddress();
        $phone = $billingAddress->getTelephone();

        return [
            'name' => [
                'given_name' => $quote->getCustomerFirstname(),
                'surname' => $quote->getCustomerLastname()
            ],
            'email' => $quote->getCustomerEmail(),
            'phone_number' => $phone !== null ? preg_replace('/[^0-9]/', '', $phone) : null,
            'customer_id' => $customerId
        ];
    }

    /**
     * Build Guest Payer object for PayPal order creation
     *
     * @param Quote $quote
     * @return array
     */
    public function buildGuestPayer(Quote $quote) : array
    {
        $billingAddress = $quote->getBillingAddress();
        $phone = $billingAddress->getTelephone();

        return [
            'name' => [
                'given_name' => $billingAddress->getFirstname(),
                'surname' => $billingAddress->getLastname()
            ],
            'email' => $billingAddress->getEmail(),
            'phone_number' => $phone !== null ? preg_replace('/[^0-9]/', '', $phone) : null
        ];
    }
}
