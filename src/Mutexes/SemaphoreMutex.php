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
     */
    public function acquire(string $lockPath = null): void
    {
        if (!extension_loaded('sysvsem')) {
            throw new InvalidArgumentException('Semaphore extension (sysvsem) is required');
        }

        if ($this->lockHandle) {
            return;
        }

        $keyId = crc32($this->name);
        $resource = sem_get(crc64($this->name));
        $acquired = @sem_acquire($resource, true);

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
