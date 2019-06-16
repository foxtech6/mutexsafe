<?php

namespace Foxtech;

/**
 * This interface will allow you to add your own mutex
 * to our library
 *
 * Interface MutexInterface
 * @package Foxtech
 *
 * @author foxtech <foxtech12@gmail.com>
 */
interface MutexInterface
{
    /**
     * The method should turn on the lock
     */
    public function acquire(): void ;

    /**
     * The method that should turn off the lock
     */
    public function release(): void ;
}