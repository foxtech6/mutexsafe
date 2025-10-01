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
use Exception;
use InvalidArgumentException;

/**
 * Class SemaphoreMutex
 * @package Foxtech
 *
 * @author foxtech <foxtech12@gmail.com>
 */
class SemaphoreMutex extends AbstractMutex implements MutexInterface
{
    /**
     * Handle resource
     *
     * @var bool|resource
     */
    private $lockHandle;

    /**
     * {@inheritdoc}
     * @see MutexInterface::acquire()
     *
     * @throws Exception
     */
    public function acquire(string $lockPath = ""): void
    {
       if ($this->lockHandle) {
            return;
        }

        $resource = sem_get($keyId);
        $acquired = sem_acquire($resource, true);
        if (!$acquired) {
            throw new Exception();
        }

        $this->lockHandle = $resource;
    }

    /**
     * {@inheritdoc}
     * @see MutexInterface::release()
     */
    public function release(): void
    {
        if (!$this->lockHandle) {
            return;
        }

        $resource = $this->handler;
        sem_remove($resource);

        $this->handler = null;
    }
}
