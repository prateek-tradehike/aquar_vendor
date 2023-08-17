<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ServicesConnector\Model;

use Magento\ServicesConnector\Api\ClientResolverInterface;
use Magento\ServicesConnector\Api\JwtTokenInterface;
use Magento\ServicesConnector\Exception\KeyNotFoundException;
use Magento\ServicesConnector\Api\KeyValidationInterface;

/**
 * Client resolver implementation
 */
class KeyValidation implements KeyValidationInterface
{
    /**
     * @var EnvironmentFactory
     */
    private $environmentFactory;

    /**
     * @var ClientResolverInterface
     */
    private $clientResolver;

    /**
     * @var JwtTokenInterface $jwtToken
     */
    private $jwtToken;

    /**
     * KeyValidation constructor.
     *
     * @param EnvironmentFactory $environmentFactory
     * @param ClientResolverInterface $clientResolver
     * @param JwtTokenInterface $jwtToken
     */
    public function __construct(
        EnvironmentFactory $environmentFactory,
        ClientResolverInterface $clientResolver,
        JwtTokenInterface $jwtToken
    ) {
        $this->environmentFactory = $environmentFactory;
        $this->clientResolver = $clientResolver;
        $this->jwtToken = $jwtToken;
    }

    /**
     * @inheritDoc
     */
    public function execute($extension, $environment = 'production')
    {
        $envObject = $this->environmentFactory->create($environment);
        if (empty($envObject->getApiKey($extension))) {
            throw new KeyNotFoundException(__("Api key is not found for extension '$extension'"));
        }
        if (!empty($envObject->getPrivateKey($extension))) {
            // Try to sign private key for validation - throws PrivateKeySignException
            $this->jwtToken->getSignature($envObject->getPrivateKey($extension));
            // Disable key validation for now for non MAGI extension
            return true;
        }
        $client = $this->clientResolver->createHttpClient($extension, $environment);
        $result = $client->request('GET', '/gateway/apikeycheck');

        if ($result->getStatusCode() >= 400 && $result->getStatusCode() < 600) {
            return false;
        }

        return true;
    }
}
