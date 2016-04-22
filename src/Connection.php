<?php namespace Mookofe\Tail;

use Exception;
use Mookofe\Tail\BaseOptions;
use PhpAmqpLib\Connection\AMQPConnection;

/**
 * Connection class, used to manage connection to the RabbitMQ Server
 *
 * @author Victor Cruz <cruzrosario@gmail.com>
 */
class Connection extends BaseOptions{

    /**
     * RabbitMQ server name or IP
     *
     * @var string
     */
    public $host;

    /**
     * RabbitMQ server port
     *
     * @var string
     */
    public $port;

    /**
     * RabbitMQ server username
     *
     * @var string
     */
    public $username;

    /**
     * RabbitMQ server password
     *
     * @var string
     */
    public $password;

    /**
     * RabbitMQ server consumer tag
     *
     * @var string
     */
    public $consumer_tag;

	/**
     * RabbitMQ queue creation config
     *
     * @var array
     */
    public $queue_settings;

	/**
     * RabbitMQ exchange creation config
     *
     * @var array
     */
    public $exchange_settings;

    /**
     * RabbitMQ AMQP Connection
     *
     * @var PhpAmqpLib\Connection\AMQPConnection
     */
    public $AMQPConnection;

    /**
     * RabbitMQ AMQP channel
     *
     * @var PhpAmqpLib\Connection\AMQPConnection
     */
    public $channel;


    /**
     * Connection constructor
     *
     * @param array $options  Options array to set connection
     *
     * @return Mookofe\Tail\Connection
     */
    public function __construct(array $options = null)
    {
        $this->allowedOptions = array_merge($this->allowedOptions, array('host', 'port', 'username', 'password', 'consumer_tag', 'queque_settings','exchange_settings','default_delivery'));

        if (!$options)
            $options = $this->buildConnectionOptions();

        $this->setOptions($options);
    }

    /**
     * Open a connection with the RabbitMQ Server
     *
     * @return void
     */
    public function open()
    {
        try
        {
            $this->AMQPConnection = new AMQPConnection($this->host, $this->port, $this->username, $this->password, $this->vhost);
            $this->channel = $this->AMQPConnection->channel();

			/**
		     * Declares queue, creates if needed
		     *
		     * @param string $queue
		     * @param bool $passive
		     * @param bool $durable
		     * @param bool $exclusive
		     * @param bool $auto_delete
		     * @param bool $nowait
		     * @param array $arguments
		     * @param int $ticket
		     * @return mixed|null
		     */
			$this->channel->queue_declare($this->queue_name,
					$this->queque_settings['passive'],
					$this->queque_settings['durable'],
					$this->queque_settings['exclusive'],
					$this->queque_settings['auto_delete'],
					$this->queque_settings['nowait']);

			/**
		     * Declares exchange, creates if needed
		     *
			* @param string $exchange
	        * @param string $type
	        * @param bool $passive
	        * @param bool $durable
	        * @param bool $auto_delete
	        * @param bool $internal
	        * @param bool $nowait
	        * @param array $arguments
	        * @param int $ticket
	        * @return mixed|null
			*/
			$this->channel->exchange_declare($this->exchange,
					$this->exchange_settings['type'],
					$this->exchange_settings['passive'],
					$this->exchange_settings['durable'],
					$this->exchange_settings['auto_delete'],
					$this->exchange_settings['internal'],
					$this->exchange_settings['nowait']);

            $this->channel->queue_bind($this->queue_name, $this->exchange);

			$shutdown = function () use (&$self) {
            	$self->close();
        	};
        	register_shutdown_function($shutdown);
        }
        catch (Exception $e)
        {
            throw new Exception($e);
        }
    }



    /**
     * Close the connection with the RabbitMQ server
     *
     * @return void
     */
    public function close()
    {
		try{
	        if (isset($this->AMQPConnection))
	            $this->AMQPConnection->close();
	        if (isset($this->channel))
	            $this->channel->close();
		}
		catch (Exception $e)
        {

        }
    }

}
