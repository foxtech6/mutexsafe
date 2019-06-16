# Mutex "m4z3p4"

![](https://i.ibb.co/6Wd3SKj/depositphotos-7524421-stock-illustration-saber-vector.jpg)

- This library will help you use mutex more effectively.
- Different mutex for different components are presented.
- In addition, you can add your own lockers and use within the library.

## How to use

**First example**
```php
$pdo = new \PDO('mysql:host=localhost;dbname=test', 'root', 'toor');

$factory = new \Foxtech\Competitor($pdo);
$factory->getMutex('mutex_name')->acquire();

// some code

$factory->getMutex('mutex_name')->release();
```

**Second example**
```php
$pdo = new \PDO('mysql:host=localhost;dbname=test', 'root', 'toor');

$factory = new \Foxtech\Competitor();
$factory->setHandler($pdo);

$factory->getMutex('mutex_name')->acquire();

// some code

$factory->getMutex('mutex_name')->release();
```
------------
You can also write your own mutex to a custom handler and use within our library. (**Important**: Your mutex must implement our [interface](https://github.com/foxtech6/mutex-locker/blob/master/src/foxtech/MutexInterface.php))

**Example**
```php
$factory = new \Foxtech\Competitor();
$factory->push(CustomHandler::class, YourMutex::class);
$factory->setHandler($customHandlerObject);

$factory->getMutex('mutex_name')->acquire();

// some code

$factory->getMutex('mutex_name')->release();
```


