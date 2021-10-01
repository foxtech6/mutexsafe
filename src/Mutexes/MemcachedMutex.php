<?php

/*
 * This file is part of the Foxtech package.
 *
 * (c) foxtech <foxtech12@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxtech\Mutexes;

use Foxtech\AbstractMutex;
use Foxtech\MutexInterface;
use Memcached;

/**
 * Class MemcachedMutex
 * @package Foxtech
 *
 * @author foxtech <foxtech12@gmail.com>
 *
 * @property Memcached $handler Memcached Handler
 */
class MemcachedMutex extends AbstractMutex implements MutexInterface
{
    /**
     * {@inheritdoc}
     * @see MutexInterface::acquire()
     */
    public function acquire(int $timeout = 60): void
    {
        $this->handler->add($this->name, true, $timeout);
    }

    /**
     * {@inheritdoc}
     * @see MutexInterface::release()
     */
    public function release(): void
    {
        $this->handler->delete($this->name);
    }
}
