<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the NekloEULA that is bundled with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt
 *
 * Copyright (c)  Neklo (http://store.neklo.com/)
 */
declare(strict_types=1);

namespace Neklo\Core\Helper;

use Laminas\Http\Request;
use Laminas\Stdlib\Parameters;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Neklo\Core\Serialize\Serializer;

class Sender extends AbstractHelper
{
    public const CONTACT_URL = 'https://store.neklo.com/neklo_support/index/index/';

    public const SUBSCRIBE_URL = 'https://store.neklo.com/neklo_subscribe/index/index/';

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @var ProductMetadataInterface
     */
    private ProductMetadataInterface $productMetadata;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     * @param ObjectManagerInterface $objectManager
     * @param Serializer $serializer
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadata,
        ObjectManagerInterface $objectManager,
        Serializer $serializer
    ) {
        parent::__construct($context);
        $this->productMetadata = $productMetadata;
        $this->objectManager = $objectManager;
        $this->serializer = $serializer;
    }

    /**
     * Send Data
     *
     * @param Parameters $data
     *
     * @return void
     * @throws LocalizedException
     */
    public function sendData(Parameters $data): void
    {
        $url = isset($data['url']) ? self::CONTACT_URL : self::SUBSCRIBE_URL;
        $data = $this->urlEncoder->encode($this->serializer->serialize($data));

        if (version_compare($this->productMetadata->getVersion(), '2.4.6', '<')) {
            $this->sendUsingZend($url, $data);
        } else {
            $this->sendUsingLaminas($url, $data);
        }
    }

    /**
     * Send Using Zend.
     *
     * @param string $url
     * @param string $data
     *
     * @return void
     *
     * @throws \Zend_Http_Client_Exception
     */
    private function sendUsingZend(string $url, string $data): void
    {
        $client = $this->objectManager->create(\Magento\Framework\HTTP\ZendClient::class);
        $client->setMethod(Request::METHOD_POST)
            ->setUri($url)
            ->setConfig(['maxredirects' => 0, 'timeout' => 30])
            ->setRawData($data)
            ->request();
    }

    /**
     * Send using Laminas.
     *
     * @param string $url
     * @param string $data
     *
     * @return void
     */
    private function sendUsingLaminas(string $url, string $data): void
    {
        $client = $this->objectManager->create(\Magento\Framework\HTTP\LaminasClient::class);
        $client->setMethod(Request::METHOD_POST)
            ->setUri($url)
            ->setOptions(['maxredirects' => 0, 'timeout' => 30])
            ->setRawBody($data)
            ->send();
    }
}
