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

namespace Neklo\Core\Serialize;

use InvalidArgumentException;
use Magento\Framework\App\ProductMetadataInterface as ProductMetadata;
use Magento\Framework\Exception\LocalizedException;

class Serializer implements SerializerInterface
{
    /**
     * @var ProductMetadata
     */
    private ProductMetadata $productMetadata;

    /**
     * @var array
     */
    private array $serializers;

    /**
     * @var SerializerInterface
     */
    private $currentSerializer;

    /**
     * @param ProductMetadata $productMetadata
     * @param array $serializers
     */
    public function __construct(ProductMetadata $productMetadata, array $serializers = [])
    {
        $this->productMetadata = $productMetadata;
        $this->serializers = $this->prepareSerializers($serializers);
    }

    /**
     * Serialize data into string
     *
     * @param string|int|float|bool|array|null $data
     *
     * @return string
     * @throws InvalidArgumentException
     * @throws LocalizedException
     */
    public function serialize($data): string
    {
        return (string) $this->get()->serialize($data);
    }

    /**
     * Unserialize the given string
     *
     * @param string $string
     *
     * @return string|int|float|bool|array|null
     * @throws InvalidArgumentException
     * @throws LocalizedException
     */
    public function unserialize($string)
    {
        return $this->get()->unserialize($string);
    }

    /**
     * Get Serializer
     *
     * @return SerializerInterface|null
     * @throws LocalizedException
     */
    private function get(): ?SerializerInterface
    {
        if ($this->currentSerializer === null) {
            $this->currentSerializer = $this->find();
        }

        return $this->currentSerializer;
    }

    /**
     * Find Serializer
     *
     * @return SerializerInterface|null
     * @throws LocalizedException
     */
    private function find(): ?SerializerInterface
    {
        foreach ($this->serializers as $config) {
            $magentoVersion = (string)($config['magento_version'] ?? null);
            if (!$magentoVersion || !version_compare($this->productMetadata->getVersion(), $magentoVersion, '>=')) {
                continue;
            }

            $serializer = $config['serializer'] ?? null;
            if (!$serializer instanceof SerializerInterface) {
                continue;
            }

            return $serializer;
        }

        throw new LocalizedException(__('Serializer not configured.'));
    }

    /**
     * Prepare Serializers
     *
     * @param array $processors
     *
     * @return array
     */
    private function prepareSerializers(array $processors): array
    {
        usort($processors, [$this, 'sort']);

        return $processors;
    }

    /**
     * Sort
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    private function sort(array $a, array $b): int
    {
        $aVersion = $a['magento_version'] ?? null;
        $bVersion = $b['magento_version'] ?? 0;

        return version_compare($bVersion, $aVersion);
    }
}
