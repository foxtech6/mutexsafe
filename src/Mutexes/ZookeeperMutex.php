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
use Zookeeper;
use ZookeeperException;
use LockConflictedException;
use LockAcquiringException;
use Exception;

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
    private $token;

    /**
     * {@inheritdoc}
     * @see MutexInterface::acquire()
     */
    public function acquire(string $key = null): void
    {
        if ($this->exists($key)) {
            return;
        }

        $this->createNewLock($key, $this->getUniqueToken());
    }

    /**
     * {@inheritdoc}
     * @see MutexInterface::release()
     */
    public function release(string $key = null): void
    {
        if (!$this->exists($key)) {
            return;
        }

        try {
            $this->handler->delete($key);
        } catch (ZookeeperException $exception) {
            throw new LockReleasingException($exception);
        }
    }

    /**
     * Function checks whether mutex exists
     *
     * @param string $key Mutex key
     * @return bool
     *
     * @throws ZookeeperException
     * @throws Exception
     */
    public function exists(string $key): bool
    {
        try {
            return $this->handler->get($key) === $this->getUniqueToken();
        } catch (ZookeeperException $e) {
            return false;
        }
    }

    /**
     * Creates a zookeeper node.
     *
     * @param string $node  The node which needs to be created
     * @param string $value The value to be assigned to a zookeeper node
     *
     * @throws LockConflictedException
     * @throws LockAcquiringException
     */
    private function createNewLock(string $node, string $value): void
    {
        try {
            $this->handler->create(
                $node,
                $value,
                [['perms' => Zookeeper::PERM_ALL, 'scheme' => 'world', 'id' => 'anyone']],
                Zookeeper::EPHEMERAL
            );
        } catch (ZookeeperException $ex) {
            if (Zookeeper::NODEEXISTS === $ex->getCode()) {
                throw new LockConflictedException($ex);
            }

            throw new LockAcquiringException($ex);
        }
    }

    /**
     * Get unique token for store new mutex
     *
     * @return string
     *
     * @throws Exception
     */
    private function getUniqueToken(): string
    {
        if (!$this->token) {
            $this->token = base64_encode(random_bytes(64));
        }

        return $this->token;
    }
}
