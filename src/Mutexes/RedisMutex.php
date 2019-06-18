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
use InvalidArgumentException;
use Redis;
use RedisArray;
use RedisCluster;
use Predis\Client;
use Symfony\Component\Cache\Traits\RedisProxy;
use Symfony\Component\Cache\Traits\RedisClusterProxy;

/**
 * Class MemcachedMutex
 * @package Foxtech
 *
 * @author foxtech <foxtech12@gmail.com>
 *
 * @property Redis|RedisArray|RedisCluster|Client|RedisClusterProxy $handler Memcached Handler
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
            && !$handler instanceof RedisProxy
            && !$handler instanceof RedisClusterProxy
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s() expects parameter 1 to be Redis, RedisArray, RedisCluster or Predis\Client, %s given',
                    __METHOD__,
                    \is_object($handler) ? \get_class($handler) : \gettype($handler)
                )
            );
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
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("PEXPIRE", KEYS[1], ARGV[2])
            elseif redis.call("SET", KEYS[1], ARGV[1], "NX", "PX", ARGV[2]) then
                return 1
            else
                return 0
            end
        ';

        if (
            $this->handler instanceof Redis ||
            $this->handler instanceof RedisCluster ||
            $this->handler instanceof RedisProxy ||
            $this->handler instanceof RedisClusterProxy
        ) {
            $this->handler->eval($script, [$this->name, $this->token, $timeout * 1000], 1);
        }

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

        if (
            $this->handler instanceof Redis ||
            $this->handler instanceof RedisCluster ||
            $this->handler instanceof RedisProxy ||
            $this->handler instanceof RedisClusterProxy
        ) {
            $this->handler->eval($script, [$this->name, $this->token], 1);
        }

        if ($this->handler instanceof RedisArray) {
            $this->handler->_instance($this->handler->_target($this->name))->eval(
                $script,
                [$this->token],
                1
            );
        }
        if ($this->handler instanceof Client) {
            $this->handler->eval(...[$script, 1, $this->name, $this->token]);
        }
    }
}
