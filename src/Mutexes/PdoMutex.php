<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Mykhailo Bavdys <bavdysmyh@ukr.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxtech\Mutexes;

use Foxtech\AbstractMutex;
use Foxtech\MutexInterface;
use PDO;

/**
 * Class PdoMutex
 * @package Foxtech
 *
 * @author foxtech <foxtech12@gmail.com>
 *
 * @property PDO $handler PDO Handler
 */
class PdoMutex extends AbstractMutex implements MutexInterface
{
    /**
     * {@inheritdoc}
     * @see MutexInterface::acquire()
     */
    public function acquire(int $timeout = 30): void
    {
        $stmt = $this->handler->prepare('SELECT GET_LOCK(?, ?)');
        $stmt->execute([$this->name, $timeout]);
    }

    /**
     * {@inheritdoc}
     * @see MutexInterface::release()
     */
    public function release(): void
    {
        $stmt = $this->handler->prepare('SELECT RELEASE_LOCK(?)');
        $stmt->execute([$this->name]);
    }
}
