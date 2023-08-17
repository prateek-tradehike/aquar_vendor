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

namespace Neklo\Core\Block\System\Extension;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Neklo\Core\Helper\Extension;
use Neklo\Core\Model\Feed\Extension as FeedExtension;

class ExtensionList extends Template
{
    /**
     * @var array
     */
    private array $feedData = [];

    /**
     * @var Extension
     */
    private Extension $extensionHelper;

    /**
     * @var FeedExtension
     */
    private FeedExtension $feedExtension;

    /**
     * @param Extension $extensionHelper
     * @param FeedExtension $feedExtension
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Extension $extensionHelper,
        FeedExtension $feedExtension,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->feedExtension = $feedExtension;
        $this->extensionHelper = $extensionHelper;
    }

    /**
     * Can Show Extension
     *
     * @param string $code
     *
     * @return bool
     */
    public function canShowExtension(string $code): bool
    {
        $feedData = $this->_getExtensionInfo(strtolower($code));

        return !!count($feedData);
    }

    /**
     * Get Extensions List
     *
     * @return array
     */
    public function getExtensionList(): array
    {
        return $this->extensionHelper->getModuleConfigList();
    }

    /**
     * Get Ext Name
     *
     * @param string $code
     *
     * @return string
     */
    public function getExtensionName(string $code): string
    {
        $feedData = $this->_getExtensionInfo(strtolower($code));

        if (!array_key_exists('name', $feedData)) {
            return $code;
        }

        return (string)$feedData['name'];
    }

    /**
     * Is Extension Version Outdated
     *
     * @param string $code
     * @param array $config
     *
     * @return bool
     */
    public function isExtensionVersionOutdated(string $code, array $config): bool
    {
        $currentVersion = $this->getExtensionVersion($config);
        $lastVersion = $this->getLastExtensionVersion($code);

        return version_compare($currentVersion, $lastVersion) === -1;
    }

    /**
     * Get Extension Version
     *
     * @param array $config
     *
     * @return string
     */
    public function getExtensionVersion(array $config): string
    {
        $version = (string)$config['setup_version'];
        if (!$version) {
            return '';
        }

        return $version;
    }

    /**
     * Get Last Extension Version
     *
     * @param string $code
     *
     * @return string
     */
    public function getLastExtensionVersion(string $code): string
    {
        $feedData = $this->_getExtensionInfo(strtolower($code));
        if (!array_key_exists('version', $feedData)) {
            return '0';
        }

        return (string)$feedData['version'];
    }

    /**
     * Get Image Url
     *
     * @param string $code
     *
     * @return string|null
     */
    public function getImageUrl(string $code): ?string
    {
        $feedData = $this->_getExtensionInfo($code);

        if (!array_key_exists('image', $feedData)) {
            return null;
        }

        return (string)$feedData['image'];
    }

    /**
     * Get Extension Info
     *
     * @param string $code
     *
     * @return array
     */
    private function _getExtensionInfo(string $code): array
    {
        if (!count($this->feedData)) {
            $this->feedData = $this->feedExtension->getFeed();
        }

        if (!array_key_exists($code, $this->feedData)) {
            return [];
        }

        return (array)$this->feedData[$code];
    }
}
