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

namespace Neklo\Core\Model\Feed;

use Magento\Framework\App\Cache;
use Magento\Framework\HTTP\Client\Curl;
use Neklo\Core\Serialize\Serializer\Json as Serializer;

class Extension
{
    public const FEED_URL = 'https://store.neklo.com/feed.json';
    public const CACHE_ID = 'NEKLO_EXTENSION_FEED';
    public const CACHE_LIFETIME = 172800;

    /**
     * @var Cache
     */
    private Cache $cacheManager;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Curl
     */
    private Curl $curl;

    /**
     * @param Cache $cacheManager
     * @param Serializer $serializer
     * @param Curl $curl
     */
    public function __construct(
        Cache $cacheManager,
        Serializer $serializer,
        Curl $curl
    ) {
        $this->cacheManager = $cacheManager;
        $this->serializer = $serializer;
        $this->curl = $curl;
    }

    /**
     * Get Feed Array
     *
     * @return array
     */
    public function getFeed(): array
    {
        if (!$feed = $this->cacheManager->load(self::CACHE_ID)) {
            $feed = $this->getFeedFromResource();
            if (!empty($this->serializer->unserialize($feed))) {
                $this->save($feed);
            }
        }

        $feedArray = $this->serializer->unserialize($feed);
        if (!is_array($feedArray)) {
            $feedArray = [];
        }

        return $feedArray;
    }

    /**
     * Get Feed
     *
     * @return string
     */
    private function getFeedFromResource(): string
    {
        $params = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => 'Content-Type: application/json'
        ];

        $this->curl->post(self::FEED_URL, $params);
        if ($this->curl->getStatus() == 200) {
            $result = $this->curl->getBody();
        } else {
            $result = '{}';
        }

        return $result;
    }

    /**
     * Save Feed To Cache
     *
     * @param string $feed
     *
     * @return void
     */
    private function save(string $feed): void
    {
        $this->cacheManager->save($feed, self::CACHE_ID, [], self::CACHE_LIFETIME);
    }
}
