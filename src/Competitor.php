<?php

/*
 * This file is part of the Foxtech package.
 *
 * (c) foxtech <foxtech12@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxtech;

use Foxtech\Mutexes\MemcachedMutex;
use Foxtech\Mutexes\PdoMutex;
use Foxtech\Mutexes\RedisMutex;
use Foxtech\Mutexes\ZookeeperMutex;
use InvalidArgumentException;
use PDO;
use Memcached;
use Redis;
use RedisArray;
use RedisCluster;
use Predis\Client;
use Zookeeper;

/**
 * Class Competitor
 * @package Foxtech
 *
 * @author foxtech <foxtech12@gmail.com>
 */
class Competitor
{
    /**
     * Mapper handlers and mutexes
     *
     * @var array
     */
    protected $handlers = [
        PDO::class          => PdoMutex::class,
        Memcached::class    => MemcachedMutex::class,
        Redis::class        => RedisMutex::class,
        RedisArray::class   => RedisMutex::class,
        RedisCluster::class => RedisMutex::class,
        Client::class       => RedisMutex::class,
        Zookeeper::class    => ZookeeperMutex::class,
    ];

    /**
     * Mutexes that are being used now
     *
     * @var array
     */
    private $mutexes = [];

    /**
     * Handler that is being used now
     *
     * @var
     */
    private $handler;

    /**
     * Competitor constructor.
     * @param mixed $handler Handler for handle mutex
     */
    public function __construct($handler = null)
    {
        if ($handler) {
            $this->setHandler($handler);
        }
    }

    /**
     * Set handler for used
     *
     * @param mixed $handler Handler for handle mutex
     *
     * @throws InvalidArgumentException
     */
    public function setHandler($handler): void
    {
        if (!array_key_exists(get_class($handler), $this->handlers)) {
            throw new InvalidArgumentException(sprintf(
                'The %s you want to use is not in the list of implementing classes',
                get_class($handler)
            ));
        }
      
        $this->handler = $handler;
    }

    /**
     * Get mutex for work
     *
     * @param string $name Get mutex by name
     *
     * @return MutexInterface
     */
    public function getMutex(string $name): MutexInterface
    {
        if (!isset($this->mutexes[$name])) {
            $mutexClass = $this->handlers[get_class($this->handler)];
            $this->mutexes[$name] = new $mutexClass(...$mutexClass);
        }

        return $this->mutexes[$name];
    }

    /**
     * Add to the list of custom mutexes
     *
     * @param string $handlerClass Handler which you want to add to the list
     * @param string $mutexClass   Mutex which you want to add to the list
     *
     * @throws InvalidArgumentException
     */
    public function push(string $handlerClass, string $mutexClass): void
    {
        if (!is_a($mutexClass, MutexInterface::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'The %s you want to add does not implement the interface %s',
                $mutexClass,
                MutexInterface::class
            ));
        }

        $this->handlers[$handlerClass] = $mutexClass;
    }
}
