<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ServicesId\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Validates API keys and connection to merchant registry
 */
class MerchantRegistryConnectionValidator
{
    private const CONFIG_PATH_SANDBOX_GATEWAY_URL = 'services_connector/sandbox_gateway_url';
    private const CONFIG_PATH_KEY_VALIDATOR_PATH = 'services_connector/services_id/registry_cloudfront_validator_path';
    private const URLS_FOR_CLOUDFRONT_VALIDATION = ['https://commerce-beta.adobe.io/'];

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var ServicesConfigInterface
     */
    private $servicesConfig;

    /**
     * @var ServicesClientInterface
     */
    private $servicesClient;

    /**
     * @param ScopeConfigInterface $config
     * @param ServicesConfigInterface $servicesConfig
     * @param ServicesClientInterface $servicesClient
     */
    public function __construct(
        ScopeConfigInterface $config,
        ServicesConfigInterface $servicesConfig,
        ServicesClientInterface $servicesClient
    ) {
        $this->config = $config;
        $this->servicesConfig = $servicesConfig;
        $this->servicesClient = $servicesClient;
    }

    /**
     * Validate API keys against merchant registry
     *
     * @param string $environment
     * @return string
     */
    public function validate(string $environment) : string
    {
        $message = ServicesConfigMessage::OK;
        try {
            $useCloudfrontValidation = $environment === 'sandbox' && in_array(
                $this->config->getValue(self::CONFIG_PATH_SANDBOX_GATEWAY_URL),
                self::URLS_FOR_CLOUDFRONT_VALIDATION,
                true
            );
            $url = $useCloudfrontValidation
                ? $this->config->getValue(self::CONFIG_PATH_KEY_VALIDATOR_PATH)
                : $this->servicesConfig->getRegistryApiUrl('ping');
            $result = $this->servicesClient->request('GET', $url, null, [], $environment);
            if ((int) $result['status'] !== 200) {
                $message = $result['message'] ?? ServicesConfigMessage::ERROR_REQUEST_FAILED;
            }
        } catch (\Exception $e) {
            $message = ServicesConfigMessage::ERROR_REQUEST_FAILED;
        }
        return $message;
    }
}
