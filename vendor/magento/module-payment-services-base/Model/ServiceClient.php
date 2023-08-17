<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\PaymentServicesBase\Model;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\ServicesConnector\Api\ClientResolverInterface;
use Magento\ServicesConnector\Exception\KeyNotFoundException;
use Magento\ServicesConnector\Api\KeyValidationInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ServicesConnector\Exception\PrivateKeySignException;
use Magento\Framework\App\CacheInterface;

/**
 * Generic SaaS Service Client
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceClient implements ServiceClientInterface
{
    /**
     * Extension name for Services Connector
     */
    private const EXTENSION_NAME = 'Magento_PaymentServicesBase';

    /**
     * @var ClientResolverInterface
     */
    private $clientResolver;

    /**
     * @var KeyValidationInterface
     */
    private $keyValidator;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CacheInterface
     */
    private $cache;

    private const SCOPE_TYPE = 'website';

    private const AUTH_REQUEST_EXCEPTION = 'error during authorize request';

    private const NO_ACTIVE_ACCOUNT_EXCEPTION = 'mage id does not have any active PayPal accounts registered';

    /**
     * @var int[]
     */
    private $successfulResponseCodes = [200, 201, 202, 204];

    /**
     * @param ClientResolverInterface $clientResolver
     * @param KeyValidationInterface $keyValidator
     * @param Config $config
     * @param Json $serializer
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param CacheInterface $cache
     */
    public function __construct(
        ClientResolverInterface $clientResolver,
        KeyValidationInterface $keyValidator,
        Config $config,
        Json $serializer,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        CacheInterface $cache
    ) {
        $this->clientResolver = $clientResolver;
        $this->keyValidator = $keyValidator;
        $this->config = $config;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->cache = $cache;
    }

    /**
     * Make request to service.
     *
     * @param array $headers
     * @param string $path
     * @param string $httpMethod
     * @param string $data
     * @param string $requestContentType
     * @param string $environment
     * @return array
     * @throws NoSuchEntityException
     * @throws PrivateKeySignException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function request(
        array $headers,
        string $path,
        string $httpMethod = \Magento\Framework\App\Request\Http::METHOD_POST,
        string $data = '',
        string $requestContentType = 'json',
        string $environment = ''
    ): array {
        $result = [];
        try {
            $environment = $environment ?: $this->config->getEnvironmentType();
            $client = $this->clientResolver->createHttpClient(
                self::EXTENSION_NAME,
                $environment
            );
            $options = [
                'headers' => array_merge(
                    $headers,
                    $this->prepareHeaders($client, $headers, $environment)
                ),
                'body' => $data
            ];
            if ($this->validateApiKey($environment)) {
                $response = $client->request($httpMethod, $path, $options);
                $isSuccessful = in_array($response->getStatusCode(), $this->successfulResponseCodes);
                $result = [
                    'is_successful' => $isSuccessful,
                    'status' => $response->getStatusCode()
                ];
                if ($isSuccessful) {
                    if ($requestContentType === 'json') {
                        try {
                            $result = array_merge(
                                $result,
                                $this->serializer->unserialize($response->getBody()->getContents())
                            );
                        } catch (\InvalidArgumentException $e) {
                            $result = [
                                'is_successful' => false,
                                'status' => 500,
                                'message' => $e->getMessage()
                            ];
                            $this->logger->error(
                                'An error occurred.',
                                [
                                    'request' => [
                                        'host' => (string) $client->getConfig('base_uri'),
                                        'path' => $path,
                                        'headers' => $options['headers'],
                                        'method' => $httpMethod,
                                        'body' => $data,
                                    ],
                                    'response' => [
                                        'body' => $response->getBody()->getContents(),
                                        'statusCode' => $response->getStatusCode(),
                                    ],
                                ],
                            );
                        }
                    } else {
                        $result = array_merge(
                            $result,
                            [
                                'content_body' => $response->getBody()->getContents(),
                                'content_disposition' => $response->getHeaderLine('Content-Disposition'),
                                'content_length' => $response->getHeaderLine('Content-Length'),
                                'content_type' => $response->getHeaderLine('Content-Type')
                            ]
                        );
                    }
                } else {
                    $exceptionMessage = $response->getBody()->getContents();
                    $result = [
                        'is_successful' => false,
                        'status' => $response->getStatusCode(),
                        'message' => $exceptionMessage
                    ];
                    if ($exceptionMessage === self::AUTH_REQUEST_EXCEPTION
                        || $exceptionMessage === self::NO_ACTIVE_ACCOUNT_EXCEPTION) {
                        $this->cache->remove('paypal_sdk_params');
                    }
                    $this->logger->error(
                        'An error occurred.',
                        [
                            'request' => [
                                'host' => (string) $client->getConfig('base_uri'),
                                'path' => $path,
                                'headers' => $options['headers'],
                                'method' => $httpMethod,
                                'body' => $data,
                            ],
                            'response' => [
                                'body' => $response->getBody()->getContents(),
                                'statusCode' => $response->getStatusCode(),
                            ],
                        ]
                    );
                }
            } else {
                $result = [
                    'status' => 403,
                    'statusText' => 'FORBIDDEN',
                    'message' => 'Magento API Key is invalid'
                ];
                $this->logger->error('API Key Validation failed.');
            }
        } catch (KeyNotFoundException $e) {
            $result = [
                'status' => 403,
                'statusText' => 'FORBIDDEN',
                'message' => 'Magento API Key not found'
            ];
            $this->logger->error('API Key Validation failed.', [$e->getMessage()]);
        } catch (GuzzleException | InvalidArgumentException $e) {
            $result = [
                'status' => 500,
                'statusText' => 'INTERNAL_SERVER_ERROR',
                'message' => 'An error occurred'
            ];
            $this->logger->error($e->getMessage());
        }
        return $result;
    }

    /**
     * Validate the API Gateway Key
     *
     * @param string $environment
     * @return bool
     * @throws KeyNotFoundException
     * @throws InvalidArgumentException
     */
    private function validateApiKey(string $environment): bool
    {
        return $this->keyValidator->execute(
            self::EXTENSION_NAME,
            $environment
        );
    }

    /**
     * Prepare request headers.
     *
     * @param Client $client
     * @param array $headers
     * @param string $environment
     * @return array
     * @throws NoSuchEntityException
     */
    private function prepareHeaders(Client $client, array $headers, string $environment): array
    {
        $defaultUserAgent = $client->getConfig('headers')['User-Agent'];
        return [
            'x-mp-merchant-id' => $this->config->getMerchantId($environment),
            'x-saas-id' => $this->config->getServicesEnvironmentId(),
            'x-scope-type' => self::SCOPE_TYPE,
            'x-scope-id' => $headers['x-scope-id'] ?? $this->storeManager->getStore()->getWebsiteId(),
            'User-Agent' => sprintf('%s PaymentServices/%s', $defaultUserAgent, $this->config->getVersion())
        ];
    }
}
