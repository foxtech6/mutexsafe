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
use InvalidArgumentException;
use Redis;
use RedisArray;
use RedisCluster;
use Predis\Client;

/**
 * Class MemcachedMutex
 * @package Foxtech
 *
 * @author foxtech <foxtech12@gmail.com>
 *
 * @property Redis|RedisArray|RedisCluster|Client $handler Memcached Handler
 */
class RedisMutex extends AbstractMutex implements MutexInterface
{
    private $token;

    /**
     * {@inheritdoc}
     * @see AbstractMutex::__construct()
     */
    public function __construct($handler, string $name)
    {
        if (!$handler instanceof Redis
            && !$handler instanceof RedisArray
            && !$handler instanceof RedisCluster
            && !$handler instanceof Client
        ) {
            throw new InvalidArgumentException(sprintf(
                '%s() expects parameter 1 to be Redis, RedisArray, RedisCluster or Predis\Client, %s given',
                __METHOD__,
                is_object($handler) ? get_class($handler) : gettype($handler)
            ));
        }

        parent::__construct($handler, $name);
    }

    /**
     * {@inheritdoc}
     * @see MutexInterface::acquire()
     */
    public function acquire(int $timeout = 30): void
    {
        if (!$this->token) {
            $this->token = base64_encode(random_bytes(32));
        }

        $script = '
            if redis.call("GET", KEYS[1]) === ARGV[1] then
                return redis.call("PEXPIRE", KEYS[1], ARGV[2])
            end
        ';

        $this->run($script, $timeout);
    }

    /**
     * {@inheritdoc}
     * @see MutexInterface::release()
     */
    public function release(): void
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';

        $this->run($script);
    }

    /**
     * Execute redis script
     *
     * @param string   $script  Execution script
     * @param int|null $timeout TTL scripts
     */
    private function run(string $script, int $timeout = null): void
    {
        $timeout = $timeout ?? 0;

        if ($this->handler instanceof RedisArray) {
            $this->handler->_instance($this->handler->_target($this->name))->eval(
                $script,
                [$this->token, $timeout * 1000],
                1
            );
        }

        if ($this->handler instanceof Client) {
            $this->handler->eval(...[$script, 1, $this->name, $this->token, $timeout * 1000]);
        }
    }
}
