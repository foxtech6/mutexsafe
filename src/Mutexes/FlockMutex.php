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
            preg_replace('/[^a-z0-9\._-]+/i', '-', $this->name),
            strtr(
                substr(base64_encode(hash('sha256', $this->name, true)), 0, 7), '/', '_'
            )
        );

        set_error_handler(function ($type, $msg) use (&$error) { $error = $msg; });

        if (!$handle = fopen($fileName, 'r+') ?: fopen($fileName, 'r')) {
            if ($handle = fopen($fileName, 'x')) {
                chmod($fileName, 0666);
            } elseif (!$handle = fopen($fileName, 'r+') ?: fopen($fileName, 'r')) {
                usleep(100); // Give some time for chmod() to complete
                $handle = fopen($fileName, 'r+') ?: fopen($fileName, 'r');
            }
        }

        restore_error_handler();

        if (!$handle) {
            // TODO: change customize exception
            throw new Exception($error, 0, null);
        }
        // On Windows, even if PHP doc says the contrary, LOCK_NB works, see
        // https://bugs.php.net/54129
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
