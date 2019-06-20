<?php

/*
 * This file is part of the Foxtech package.
 *
 * (c) Mykhailo Bavdys <bavdysmyh@ukr.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxtech\Mutexes;

use Foxtech\AbstractMutex;
use Foxtech\MutexInterface;
use Zookeeper;

/**
 * Class PdoMutex
 * @package Foxtech
 *
 * @author foxtech <foxtech12@gmail.com>
 *
 * @property Zookeeper $handler Zookeeper Handler
 */
class ZookeeperMutex extends AbstractMutex implements MutexInterface
{
    /**
     * {@inheritdoc}
     * @see MutexInterface::acquire()
     */
    public function acquire(): void
    {

    }

    /**
     * {@inheritdoc}
     * @see MutexInterface::release()
     */
    public function release(): void
    {

    }
}
