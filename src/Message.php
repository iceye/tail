<?php namespace Mookofe\Tail;

use Config;
use Mookofe\Tail\Connection;
use Mookofe\Tail\BaseOptions;
use Illuminate\Config\Repository;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPConnection;

/**
 * Message class, used to manage messages back and forth with the server
 *
 * @author Victor Cruz <cruzrosario@gmail.com>
 */
class Message extends BaseOptions {

	/**
     * Message to be send or received from the queue server
     *
     * @var string
     */
    public $message = null;

	public static  $connection = null;

	public static  $msgObj = null;

    /**
     * Message constructor
     *
     * @param array $options  Options array to get validated
     *
     * @return Mookofe\Tail\Message
     */
    public function __construct(Repository $config, array $options = NULL)
    {
		parent::__construct($config);

        if ($options){
            $this->setOptions($options);
		}

    }

	public function connect(){
		if(self::$connection == null){
			try
	        {
				self::$connection = new Connection($this->buildConnectionOptions());
				self::$connection->open();
			}
	        catch (Exception $e)
	        {
	            self::$connection->close();
	            throw new Exception($e);
	        }
		}

		if(self::$msgObj == null){
			self::$msgObj = new AMQPMessage("", array('content_type' => 'text/plain', 'delivery_mode' => $this->default_delivery_mode==1?1:2));
		}
	}

	public function close(){
		self::$connection->close();
	}

    /**
     * Add a message directly to the queue server
     *
     * @param string $queue_name  Queue name on RabbitMQ
     * @param string $message  Message to be add to the queue server
     * @param array $options  Options values for message to add
     *
     * @return void
     */
    public function add($queue_name, $message, array $options = NULL)
    {
		$this->queue_name = $queue_name;
        $this->message = $message;

        if ($options)
            $this->setOptions($options);

        $this->save();
    }

    /**
     * Save the current message instance into de queue server
     *
     * @return void
     */
    public function save()
    {
        try
        {
			//Connect only the first time...
			$this->connect();

			self::$msgObj->setBody($this->message);
            self::$connection->channel->basic_publish(self::$msgObj, $this->exchange, $this->queue_name);

        }
        catch (Exception $e)
        {
            self::$connection->close();
            throw new Exception($e);
        }
    }

}
