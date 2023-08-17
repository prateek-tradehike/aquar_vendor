<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SaaSCommon\Model\Http\Command;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Magento\SaaSCommon\Model\Exception\UnableSendData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\SaaSCommon\Model\Http\Converter\Factory;
use Magento\SaaSCommon\Model\Http\ConverterInterface;
use Magento\ServicesConnector\Api\ClientResolverInterface;
use Magento\ServicesConnector\Exception\PrivateKeySignException;
use Magento\ServicesId\Model\ServicesConfigInterface;
use Magento\SaaSCommon\Model\Logging\SaaSExportLoggerInterface as LoggerInterface;

/**
 * Class responsible for call execution to SaaS Service
 */
class SubmitFeed
{
    /**
     * Config paths
     */
    const ROUTE_CONFIG_PATH = 'magento_saas/routes/';
    const ENVIRONMENT_CONFIG_PATH = 'magento_saas/environment';

    /**
     * Extension name for Services Connector
     */
    const EXTENSION_NAME = 'Magento_DataExporter';

    /**
     * @var ClientResolverInterface
     */
    private $clientResolver;

    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var ServicesConfigInterface
     */
    private $servicesConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $extendedLog;

    /**
     * @param ClientResolverInterface $clientResolver
     * @param Factory $converterFactory
     * @param ScopeConfigInterface $config
     * @param ServicesConfigInterface $servicesConfig
     * @param LoggerInterface $logger
     * @param bool $extendedLog
     */
    public function __construct(
        ClientResolverInterface $clientResolver,
        Factory $converterFactory,
        ScopeConfigInterface $config,
        ServicesConfigInterface $servicesConfig,
        LoggerInterface $logger,
        bool $extendedLog = false
    ) {
        $this->clientResolver = $clientResolver;
        $this->converter = $converterFactory->create();
        $this->config = $config;
        $this->servicesConfig = $servicesConfig;
        $this->logger = $logger;
        $this->extendedLog = $extendedLog;
    }

    /**
     * Build URL to SaaS Service
     *
     * @param string $feedName
     * @return string
     */
    private function getUrl(string $feedName) : string
    {
        $route = '/' . $this->config->getValue(self::ROUTE_CONFIG_PATH . $feedName) . '/';
        $environmentId = $this->servicesConfig->getEnvironmentId();
        return $route . $environmentId;
    }

    /**
     * Execute call to SaaS Service
     *
     * @param string $feedName
     * @param array $data
     * @return bool
     * @throws UnableSendData
     */
    public function execute(string $feedName, array $data) : bool
    {
        $result = false;
        try {
            $client = $this->clientResolver->createHttpClient(
                self::EXTENSION_NAME,
                $this->config->getValue(self::ENVIRONMENT_CONFIG_PATH)
            );
            $headers = [
                'Content-Type' => $this->converter->getContentMediaType()
            ];
            if (null !== $this->converter->getContentEncoding()) {
                $headers['Content-Encoding'] = $this->converter->getContentEncoding();
            }
            $body = $this->converter->toBody($data);
            $options = [
                'headers' => $headers,
                'body' => $body
            ];

            if ($this->servicesConfig->isApiKeySet()) {
                $response = $client->request(\Zend_Http_Client::POST, $this->getUrl($feedName), $options);
                $result = !($response->getStatusCode() >= 500);
                if ($response->getStatusCode() !== 200) {
                    $this->logger->error(
                        'Export error. API request was not successful.',
                        $this->prepareLog($client, $response, $feedName, $data)
                    );
                }
            } else {
                $this->logger->error('API Keys Validation Failed');
                throw new UnableSendData('Unable to send data to service');
            }
        } catch (GuzzleException $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            throw new UnableSendData('Unable to send data to service');
        }  catch (PrivateKeySignException $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            throw new UnableSendData('Unable to send data to service');
        }

        return $result;
    }

    /**
     * @param Client $client
     * @param ResponseInterface $response
     * @param string $feedName
     * @param $payload
     * @return array
     */
    private function prepareLog(Client $client, ResponseInterface $response, string $feedName, $payload): array
    {
        $clientConfig = $client->getConfig();

        $log = [
            'status_code' => $response->getStatusCode(),
            'reason' => $response->getReasonPhrase(),
            'url' => $this->getUrl($feedName),
            'base_uri'=> $clientConfig['base_uri']
                ? $clientConfig['base_uri']->__toString() : 'base uri wasn\'t set',
            'response' => $response->getBody()->getContents()
        ];

        if (true === $this->extendedLog) {
            $log['headers'] = $clientConfig['headers'] ?? 'no headers';
            $log['payload'] = $payload;
        }
        return $log;
    }
}
