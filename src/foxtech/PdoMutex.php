<?php

namespace Foxtech;

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
    public function acquire($timeout = 30): void
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
