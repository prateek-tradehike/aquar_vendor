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

namespace Neklo\Core\Model;

use Magento\AdminNotification\Model\Feed as BaseFeed;
use Magento\AdminNotification\Model\InboxFactory;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Neklo\Core\Helper\Config;
use Neklo\Core\Helper\Extension;
use Neklo\Core\Model\Source\Subscription\Type;
use SimpleXMLElement;

class Feed extends BaseFeed
{
    public const XML_USE_HTTPS_PATH    = 'neklo_core/notification/use_https';
    public const XML_FEED_URL_PATH     = 'neklo_core/notification/feed_url';
    public const XML_FREQUENCY_PATH    = 'neklo_core/notification/frequency';

    public const LAST_CHECK_CACHE_KEY  = 'neklo_core_admin_notifications_last_check';

    /**
     * @var Config
     */
    private Config $configHelper;

    /**
     * @var Extension
     */
    private Extension $extensionHelper;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ConfigInterface $backendConfig
     * @param InboxFactory $inboxFactory
     * @param CurlFactory $curlFactory
     * @param DeploymentConfig $deploymentConfig
     * @param ProductMetadataInterface $productMetadata
     * @param UrlInterface $urlBuilder
     * @param Extension $extensionHelper
     * @param Config $configHelper
     * @param Escaper $escaper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ConfigInterface $backendConfig,
        InboxFactory $inboxFactory,
        CurlFactory $curlFactory,
        DeploymentConfig $deploymentConfig,
        ProductMetadataInterface $productMetadata,
        UrlInterface $urlBuilder,
        Extension $extensionHelper,
        Config $configHelper,
        Escaper $escaper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $backendConfig,
            $inboxFactory,
            $curlFactory,
            $deploymentConfig,
            $productMetadata,
            $urlBuilder,
            $resource,
            $resourceCollection,
            $data
        );
        $this->extensionHelper = $extensionHelper;
        $this->configHelper = $configHelper;
        $this->escaper = $escaper;
    }

    /**
     * Get Frequency
     *
     * @return int
     */
    public function getFrequency(): int
    {
        return (int)$this->_backendConfig->getValue(self::XML_FREQUENCY_PATH) * 3600;
    }

    /**
     * Get Last Update
     *
     * @return int
     */
    public function getLastUpdate(): int
    {
        return (int)$this->_cacheManager->load(self::LAST_CHECK_CACHE_KEY);
    }

    /**
     * Saves last time of update
     *
     * @return self
     */
    public function setLastUpdate(): self
    {
        $this->_cacheManager->save(time(), self::LAST_CHECK_CACHE_KEY);

        return $this;
    }

    /**
     * Getting Feed URL
     *
     * @return string
     */
    public function getFeedUrl(): string
    {
        $path = $this->_backendConfig->isSetFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://';
        if ($this->_feedUrl === null) {
            $this->_feedUrl = $path . $this->_backendConfig->getValue(self::XML_FEED_URL_PATH);
        }

        return $this->_feedUrl;
    }

    /**
     * Check Updates
     *
     * @return $this
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function checkUpdate(): self
    {
        if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
            return $this;
        }

        $feedData = [];

        $feedXml = $this->getFeedData();

        $installDate = strtotime($this->_deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE));

        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            /** @var SimpleXMLElement $item */
            foreach ($feedXml->channel->item as $item) {
                if (!$this->isAllowedItem($item)) {
                    continue;
                }
                $itemPublicationDate = strtotime((string)$item->pubDate);
                if ($installDate <= $itemPublicationDate) {
                    $feedData[] = [
                        'severity' => (int)$item->severity,
                        'date_added' => date('Y-m-d H:i:s', $itemPublicationDate),
                        'title' => $this->escaper->escapeHtml((string)$item->title),
                        'description' => $this->escaper->escapeHtml((string)$item->description),
                        'url' => $this->escaper->escapeHtml((string)$item->link),
                    ];
                }
            }

            if ($feedData) {
                $this->_inboxFactory->create()->parse(array_reverse($feedData));
            }
        }

        $this->setLastUpdate();

        return $this;
    }

    /**
     * Is Allowed Item
     *
     * @param SimpleXMLElement $item
     *
     * @return bool
     */
    public function isAllowedItem(SimpleXMLElement $item): bool
    {
        $itemType = $item->type ? $item->type : Type::INFO_CODE;
        $allowedTypeList = $this->configHelper->getNotificationTypeList();
        if ($itemType == Type::UPDATE_CODE) {
            if (in_array(Type::UPDATE_ALL_CODE, $allowedTypeList)) {
                return true;
            }

            if (in_array(Type::UPDATE_CODE, $allowedTypeList)) {
                $installedList = array_keys($this->extensionHelper->getModuleList());
                $isPresent = false;
                foreach ($item->extension->children() as $extensionCode) {
                    if (in_array((string)$extensionCode, $installedList)) {
                        $isPresent = true;
                    }
                }

                return $isPresent;
            }
        }

        if (!in_array($itemType, $allowedTypeList)) {
            return false;
        }

        return true;
    }
}
