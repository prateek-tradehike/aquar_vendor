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

/**
 * Interface for serializing
 *
 * @api
 */
interface SerializerInterface
{
    /**
     * Serialize data into string
     *
     * @param string|int|float|bool|array|null $data
     *
     * @return string|bool
     * @throws InvalidArgumentException
     */
    public function serialize($data);

    /**
     * Unserialize the given string
     *
     * @param string $string
     *
     * @return string|int|float|bool|array|null
     * @throws InvalidArgumentException
     */
    public function unserialize($string);
}
