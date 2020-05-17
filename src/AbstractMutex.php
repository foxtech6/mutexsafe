<?php

/*
 * This file is part of the Foxtech package.
 *
 * (c) foxtech <foxtech12@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxtech;

/**
 * The class helps to handle mutexes
 *
 * Class AbstractMutex
 * @package Foxtech
 *
 * @author foxtech <foxtech12@gmail.com>
 */
abstract class AbstractMutex
{
    /**
     * Mutex handler
     *
     * @var mixed
     */
    protected $handler;

    /**
     * Mutex name
     *
     * @var string
     */
    protected $name;

    /**
     * PdoMutex constructor
     *
     * @param mixed  $handler PDO object for work
     * @param string $name    Name fo mutex
     */
    public function __construct($handler, string $name)
    {
        $this->handler = $handler;
        $this->name = $name;
    }
}
