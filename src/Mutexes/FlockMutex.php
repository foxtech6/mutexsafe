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

use Foxtech\MutexInterface;

/**
 * Class FlockMutex
 * @package Foxtech
 *
 * @author foxtech <foxtech12@gmail.com>
 */
class FlockMutex implements MutexInterface
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
