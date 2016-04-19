<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Default AMQP Server Connection
    |--------------------------------------------------------------------------
    |
    | The name of your default AMQP server connection. This connection will
    | be used as the default for all queues operations unless a different
    | name is given when performing said operation. This connection name
    | should be listed in the array of connections below.
    |
    */
    'default' => 'default_connection',

    /*
    |--------------------------------------------------------------------------
    | Queues Connections
    |--------------------------------------------------------------------------
    */

    'connections' => array(

        'default_connection' => array(
            'host'         => 'localhost',
            'port'         => 5672,
            'username'     => 'guest',
            'password'     => 'guest',
            'vhost'        => '/',
            'exchange'     => 'amq.direct',
            'consumer_tag' => 'consumer',
			'queque_settings' => [
					'passive'=> false, //can use this to check whether an exchange exists without modifying the server state
					'durable'=> false, // if default_delivery == 1 THIS CANNOT BE TRUE! When true RabbitMQ will never lose our queue if a crash occurs - the queue will survive a broker restart, for sure true it's slower
					'exclusive'=> false, // used by only one connection and the queue will be deleted when that connection closes
					'auto_delete'=> false, //queue is deleted when last consumer unsubscribes
					'nowait'=>true //do not send a reply method
				],
			'exchange_settings' => [
					'type'=>'direct',
					'passive'=>false, //do not create exchange
					'durable'=>true, // check this value on rabbitmq conf/status, if using existing exchange this should same as exsisting feature value
					'auto_delete'=>false, //If set, the exchange is deleted when all queues have finished using it.
					'internal'=>false, //???
					'nowait'=>true //do not send a reply method
				],
            'default_delivery' => 1, // Values are 1 => DELIVERY_MODE_NON_PERSISTENT and 2 => DELIVERY_MODE_PERSISTENT
        ),
    ),
);
