<?php

namespace Foxtech;

/**
 * The class helps to handle mutexes
 *
 * Class AbstractMutex
 * @package Foxtech
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