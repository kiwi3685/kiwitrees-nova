<?php

/*
 * This file is part of the Cache package.
 *
 * Copyright (c) Daniel González
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Daniel González <daniel@desarrolla2.com>
 * @author Arnold Daniels <arnold@jasny.net>
 */

declare(strict_types=1);

namespace Desarrolla2\Cache\Packer;

use Desarrolla2\Cache\Packer\PackerInterface;
use Desarrolla2\Cache\Exception\InvalidArgumentException;

/**
 * Pack value through serialization
 */
class SerializePacker implements PackerInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * SerializePacker constructor
     *
     * @param array $options  Any options to be provided to unserialize()
     */
    public function __construct(array $options = ['allowed_classes' => true])
    {
        $this->options = $options;
    }

    /**
     * Get cache type (might be used as file extension)
     *
     * @return string
     */
    public function getType()
    {
        return 'php.cache';
    }

    /**
     * Pack the value
     * 
     * @param mixed $value
     * @return string
     */
    public function pack($value)
    {
        return serialize($value);
    }
    
    /**
     * Unpack the value
     * 
     * @param string $packed
     * @return string
     * @throws \UnexpectedValueException if he value can't be unpacked
     */
    public function unpack($packed)
    {
        if (!is_string($packed)) {
            throw new InvalidArgumentException("packed value should be a string");
        }

        return unserialize($packed, $this->options);
    }
}
