<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ServicesConnector\Model;

use Magento\ServicesConnector\Api\JwtTokenInterface;
use Magento\ServicesConnector\Exception\KeyNotFoundException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\ServicesConnector\Api\ClientResolverInterface;

/**
 * Client resolver implementation
 */
class ClientResolver implements ClientResolverInterface
{
    /**
     * @var GuzzleClientFactory
     */
    private $clientFactory;

    /**
     * @var EnvironmentFactory
     */
    private $environmentFactory;

    /**
     * @var JwtTokenInterface
     */
    private $jwtToken;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    private $productMetadata;

    /**
     * ClientResolver constructor.
     * @param GuzzleClientFactory $clientFactory
     * @param EnvironmentFactory $environmentFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        GuzzleClientFactory $clientFactory,
        EnvironmentFactory $environmentFactory,
        JwtTokenInterface $jwtToken,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->clientFactory = $clientFactory;
        $this->environmentFactory = $environmentFactory;
        $this->productMetadata = $productMetadata;
        $this->jwtToken = $jwtToken;
    }

    /**
     * @inheritDoc
     */
    public function createHttpClient($extension, $environment = 'production')
    {
        $envObject = $this->environmentFactory->create($environment);
        $apiKey = $envObject->getApiKey($extension);
        $settings = [
            'http_errors' => false,
            'headers' => [
                'magento-api-key' => $apiKey,
                'x-api-key' => $apiKey,
                'User-Agent' => sprintf(
                    'Magento Services Connector (Magento: %s)',
                    $this->productMetadata->getEdition() . ' '
                    . $this->productMetadata->getVersion()
                )
            ]
        ];

        if (empty($envObject->getPrivateKey($extension)) || $extension == 'Magento_Amazon') {
            // Fall back to MAGI
            $settings['base_uri'] = $envObject->getFallbackGatewayUrl($environment);
        } else {
            $settings['base_uri'] = $envObject->getGatewayUrl($environment);
            $privateKey = $envObject->getPrivateKey($extension);
            $settings['headers']['x-gw-signature'] = $this->jwtToken->getSignature($privateKey);
        }
        return $this->clientFactory->create($settings);
    }
}
