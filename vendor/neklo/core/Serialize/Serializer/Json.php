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

namespace Neklo\Core\Serialize\Serializer;

use InvalidArgumentException;
use Neklo\Core\Serialize\SerializerInterface;

/**
 * Serialize data to JSON, unserialize JSON encoded data
 */
class Json implements SerializerInterface
{
    /**
     * Serialize data into string
     *
     * @param string|int|float|bool|array|null $data
     *
     * @return string|bool
     * @throws InvalidArgumentException
     */
    public function serialize($data)
    {
        $result = json_encode($data);
        if (false === $result) {
            throw new InvalidArgumentException("Unable to serialize value. Error: " . json_last_error_msg());
        }

        return $result;
    }

    /**
     * Unserialize the given string
     *
     * @param string $string
     *
     * @return string|int|float|bool|array|null
     * @throws InvalidArgumentException
     */
    public function unserialize($string)
    {
        $result = json_decode($string, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Unable to unserialize value. Error: " . json_last_error_msg());
        }

        return $result;
    }
}
