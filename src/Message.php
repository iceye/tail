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

	public $connection = null;

	public $msgObj = null;

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
		if($this->connection == null){
			try
	        {
				$this->connection = new Connection($this->buildConnectionOptions());
				$this->connection->open();
			}
	        catch (Exception $e)
	        {
	            $this->connection->close();
	            throw new Exception($e);
	        }
		}

		if($this->msgObj == null){
			$this->msgObj = new AMQPMessage("", array('content_type' => 'text/plain', 'delivery_mode' => $this->default_delivery_mode==1?1:2));
		}
	}

	public function close(){
		$this->connection->close();
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
			$this->connect();

			$this->msgObj->setBody($this->message);
            $this->connection->channel->basic_publish($this->msgObj, $this->exchange, $this->queue_name);

        }
        catch (Exception $e)
        {
            $this->connection->close();
            throw new Exception($e);
        }
    }

}
