iceye/tail
=========

RabbitMQ and PHP client for Laravel and Lumen that allows you to add and listen queues messages just simple. Added configurable delivery mode.

[![Build Status](https://travis-ci.org/mookofe/tail.svg?branch=master)](https://travis-ci.org/mookofe/tail)
[![Latest Stable Version](https://poser.pugx.org/mookofe/tail/v/stable.svg)](https://packagist.org/packages/mookofe/tail)
[![License](https://poser.pugx.org/mookofe/tail/license.svg)](https://packagist.org/packages/mookofe/tail)

Features
----
  - Simple queue configuration
  - Multiple server connections
  - Add message to queues easily
  - Listen queues with useful options


Requirements
----
  - php-amqplib/php-amqplib: 2.*


Version
----
1.0.5


Installation
--------------

**Preparation**

Open your composer.json file and add the following to the require array:

```js
"iceye/tail": "1.*"
```

**Install dependencies**

```
$ php composer install
```

Or

```batch
$ php composer update
```

Integration
--------------
### Laravel
After installing the package, open your Laravel config file **config/app.php** and add the following lines.

In the $providers array add the following service provider for this package.

```batch
'Mookofe\Tail\ServiceProvider',
```

In the $aliases array add the following facade for this package.

```batch
'Tail' => 'Mookofe\Tail\Facades\Tail',
```

Add servers connection file running:

```batch
$ php artisan vendor:publish --provider="Mookofe\Tail\ServiceProvider" --tag="config"
```

### Lumen
Register the Lumen Service Provider in **bootstrap/app.php**:

```php
/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
*/

//...

$app->configure('tail-settings');
$app->register(Mookofe\Tail\LumenServiceProvider::class);

//...
```

Make sure sure `$app->withFacades();` is uncomment in your **bootstrap/app.php** file


Create a **config** folder in the root directory of your Lumen application and copy the content
from **vendor/mookofe/tail/config/tail.php** to **config/tail-settings.php**.

RabbitMQ Connections
--------------
By default the library will use the RabbitMQ installation credentials (on a fresh installation the user "guest" is created with password "guest").

To override the default connection or add more servers, edit the RabbitMQ connections file at: **config/tail-settings.php**

```php
return array(

    'default' => 'default_connection',

    'connections' => array(

        'default_connection' => array(
            'host'         => 'localhost',
            'port'         => 5672,
            'username'     => 'guest',
            'password'     => 'guest',
            'vhost'        => '/',
            'exchange'     => 'default_exchange_name',
            'consumer_tag' => 'consumer',
			'queque_settings' => [
				'passive'=> false, //can use this to check whether an exchange exists without modifying the server state
				'durable'=> false, // if default_delivery == 1 THIS CANNOT BE TRUE! When true RabbitMQ will never lose our queue if a crash occurs - the queue will survive a broker restart, for sure true it's slower
				'exclusive'=> false, // used by only one connection and the queue will be deleted when that connection closes
				'auto_delete'=> false, //queue is deleted when last consumer unsubscribes
				],
			'exchange_settings' => [
					'type'=>'direct',
					'passive'=>false, //do not create exchange
					'durable'=>false, // if default_delivery == 1 THIS CANNOT BE TRUE! When true RabbitMQ will never lose our exchange if a crash occurs - the exchange will survive a broker restart, for sure true it's slower
					'auto_delete'=>false, //If set, the exchange is deleted when all queues have finished using it.
					'nowait'=>true //do not send a reply method
				],
            'default_delivery' => 1, // Values are 1 => DELIVERY_MODE_NON_PERSISTENT and 2 => DELIVERY_MODE_PERSISTENT
        ),    
        'other_server' => array(
            'host'         => '192.168.0.10',
            'port'         => 5672,
            'username'     => 'guest',
            'password'     => 'guest',
            'vhost'        => '/',
            'exchange'     => 'default_exchange_name',
            'consumer_tag' => 'consumer',
        ),   
    ),
);
```



Adding messages to queue:
----

**Adding a simple message**

```php
    Tail::add('queue-name', 'message');
```

**Adding message changing RabbitMQ server**

```php
    Tail::add('queue-name', 'message', array('connection_name' => 'connection_name_config_file'));
```


**Adding message with different exchange**

```php
    Tail::add('queue-name', 'message', array('exchange' => 'exchange_name'));
```

**Adding message with different options**

```php
	$options = array (
		'connection_name' => 'connection_name_config_file',
		'exchange' => 'exchange_name',
		'vhost' => 'vhost',
		'delivery_mode' => DELIVERY_MODE_PERSISTENT
	);

    Tail::add('queue-name', 'message', $options);
```


**Using Tail object**

```php
	$message = new Tail::createMessage;
	$message->queue_name = 'queue-name';
	$message->message = 'message';
	$message->connection_name = 'connection_name_in_config_file';
	$message->exchange = 'exchange_name';
	$message->vhost = 'vhost';

	$message->save();
```

Listening queues:
----

**Closure based listener**

```php
Tail::listen('queue-name', function ($message) {

	//Your message logic code
});
```

**Closure listener with options**

```php
$options = array(
	'message_limit' => 50,
	'time' => 60,
	'empty_queue_timeout' => 5,
	'connection_name' => 'connection_name_in_config_file',
    'exchange' => 'exchange_name',
    'vhost' => 'vhost'
);

Tail::listenWithOptions('queue-name', $options, function ($message) {

	//Your message logic code		
});
```

**Options definitions:**

|  Name | Description  | Default value|
|---|---|---|
| queue_name | Queue name on RabbitMQ  | * Required |
| message_limit | Number of messages to be processed   | 0: Unlimited |
| time | Time in seconds the process will be running   | 0: Unlimited |
| empty\_queue\_timeout | Time in seconds to kill listening when the queue is empty | 0: Unlimited |
| connection_name | Server connection name  | Defined at connections file  |
| exchange | Exchange name on RabbitMQ Server | Specified on connections file |
| vhost | Virtual host on RabbitMQ Server | Specified on connections file |


By default the listen process will be running forever unless you specify one of the running time arguments above (message\_limit, time, empty\_queue\_timeout). They can be mixed all together, so when one of the condition	is met the process will be stopped.



License
----
This package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
