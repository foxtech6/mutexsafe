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

/**
 * Class FlockMutex
 * @package Foxtech
 *
 * @author foxtech <foxtech12@gmail.com>
 */
class FlockMutex extends AbstractMutex implements MutexInterface
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
        if ($this->lockHandle) {
            return;
        }

        $fileName = sprintf('%s/sf.%s.%s.lock',
            $lockPath ?? sys_get_temp_dir(),
            $this->name,
            strtr(substr(base64_encode(hash('sha256', $this->name, true)), 0, 7), '/', '_')
        );

        set_error_handler(function (int $type, string $msg) use (&$error) { $error = $msg; });

        restore_error_handler();

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            // TODO: change customize exception
            throw new Exception();
        }

        $this->lockHandle = $handle;
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

        flock($this->lockHandle, LOCK_UN | LOCK_NB);
        fclose($this->lockHandle);
        $this->handler = null;
    }
}
