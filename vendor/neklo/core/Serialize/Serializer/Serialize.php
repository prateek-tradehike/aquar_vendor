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
use ReflectionFunction;

/**
 * Less secure than Json implementation, but gives higher performance on big arrays. Does not unserialize objects.
 * Using this implementation is discouraged as it may lead to security vulnerabilities.
 */
class Serialize implements SerializerInterface
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
        if (is_resource($data)) {
            throw new InvalidArgumentException('Unable to serialize value.');
        }

        $serialize = new ReflectionFunction('serialize');
        $result = $serialize->invoke($data);

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
        if (false === $string || null === $string || '' === $string) {
            throw new InvalidArgumentException('Unable to unserialize value.');
        }

        set_error_handler(
            function () {
                restore_error_handler();
                throw new InvalidArgumentException('Unable to unserialize value, string is corrupted.');
            },
            E_NOTICE
        );

        $unserialize = new ReflectionFunction('unserialize');
        $result = $unserialize->invoke($string, ['allowed_classes' => false]);
        restore_error_handler();

        return $result;
    }
}
