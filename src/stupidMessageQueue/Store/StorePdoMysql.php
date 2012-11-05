<?php

namespace stupidMessageQueue\Store;

use stupidMessageQueue\Message\MessageInterface as Message;
use stupidMessageQueue\Queue\QueueInterface as Queue;
use stupidMessageQueue\Consumer\ConsumerInterface as Consumer;
use stupidMessageQueue\Exception\InvalidArgumentException as InvalidArgumentException;
use stupidMessageQueue\Exception\NotFoundException as NotFoundException;
use stupidMessageQueue\Exception\NoResultException as NoResultException;
use stupidMessageQueue\Exception\RuntimeException as RuntimeException;
use PDO as PDO;

/**
 * User: pierre
 *
 */
class StorePdoMysql implements StoreInterface
{

    const DEFAULT_TABLE_NAME = 'stupid_message_queue';

    const SQL_CREATE_TABLE = <<<EOF
CREATE TABLE IF NOT EXISTS `%s` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `state` varchar(8) NOT NULL,
  `consumer` varchar(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`),
  KEY `queue` (`queue`,`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOF;


    const SQL_INSERT = 'INSERT INTO %s (queue, content, state, created_at, updated_at) VALUES (:queue, :content, :state, NOW(), NOW())';
    const SQL_CONSUME_LOAD = 'SELECT * FROM %s WHERE queue=:queue AND consumer IS NULL ORDER BY created_at ASC LIMIT 1';
    const SQL_CONSUME = 'UPDATE %s set consumer=:consumer, state=:state, updated_at=NOW() WHERE queue=:queue AND consumer IS NULL AND id=:id';
    const SQL_CONSUMABLE_QUEUE = 'SELECT DISTINCT queue as queue FROM %s WHERE consumer IS NULL';
    const SQL_LOAD = 'SELECT * FROM %s WHERE id=:id';
    const SQL_UPDATE = 'UPDATE %s content=:content, state=:state, updated_at=NOW() WHERE id=:id';

    protected $tablename;

    protected $statements;

    /**
     * @var PDO
     */
    protected $con;

    public function __construct( PDO $con, $tablename=self::DEFAULT_TABLE_NAME ) {
        $this->con = $con;
        $this->tablename = $tablename;
        $this->statements = array();
    }

    /**
     * @param $sql
     * @return \PDOStatement
     */
    protected function getStatement( $sql ) {
        if( !isset($this->statements[$sql])) {
            $st = $this->con->prepare(
                sprintf( $sql, $this->getTable() )
            );
            $this->statements[$sql] = $st;
        }

        return $this->statements[$sql];
    }

    public function getTable() {
        return $this->tablename;
    }

    protected function treatError( \PDOStatement $st ) {
        $info = $st->errorInfo();
        throw new RuntimeException( sprintf('Error %s while running sql : %s (%s)', $info[0], $info[2], $info[1]) );
    }

    /**
     * @param \stupidMessageQueue\Queue\QueueInterface $queue
     * @param \stupidMessageQueue\Message\MessageInterface $message
     * @throws \stupidMessageQueue\Exception\InvalidArgumentException
     * @return \stupidMessageQueue\Message\MessageInterface
     */
    public function save(Queue $queue, Message $message)
    {
        if( $message->getState() != Message::STATE_NEW ) {
            throw new InvalidArgumentException('Message is not in new state');
        }
        $message->setState( Message::STATE_PENDING );
        $st = $this->getStatement( self::SQL_INSERT );
        $result = $st->execute(
            array(
                ':queue' => $queue->getName(),
                ':content' => $message->getContent(),
                ':state' => $message->getState()
            )
        );
        if( $result == false ) {
            $this->treatError( $st );
        }
        $this->hydrate($message, array('id' => $this->con->lastInsertId()));
        $st->closeCursor();

        return $message;
    }

    /**
     * @param \stupidMessageQueue\Queue\QueueInterface $queue
     * @param \stupidMessageQueue\Message\MessageInterface $message
     * @throws \stupidMessageQueue\Exception\NotFoundException
     * @return \stupidMessageQueue\Message\MessageInterface
     */
    public function load(Queue $queue, Message $message)
    {
        $st = $this->getStatement( self::SQL_LOAD );
        $result = $st->execute(
            array(
                ':id' => $message->getId(),
            )
        );
        if( $result == false ) {
            $this->treatError( $st );
        }
        $attributes = $st->fetch(PDO::FETCH_ASSOC);
        $st->closeCursor();

        if( $st->rowCount() <= 0 ) {
            throw new NotFoundException(sprintf('No message found for %d id', $message->getId()));
        }
        return $this->hydrate( $message, $attributes);
    }

    /**
     * @param \stupidMessageQueue\Consumer\ConsumerInterface $consumer
     * @param \stupidMessageQueue\Queue\QueueInterface $queue
     * @throws \stupidMessageQueue\Exception\NoResultException
     * @return \stupidMessageQueue\Message\MessageInterface
     */
    public function consume(Consumer $consumer, Queue $queue)
    {
        $this->con->beginTransaction();
        $st = $this->getStatement( self::SQL_CONSUME_LOAD );
        $result = $st->execute(
            array(
                ':queue' => $queue->getName(),
            )
        );
        if( $result == false ) {
            $this->con->rollBack();
            $this->treatError( $st );
        }
        $attributes = $st->fetch(PDO::FETCH_ASSOC);
        $st->closeCursor();

        if( $st->rowCount() <= 0 ) {
            $this->con->rollBack();
            throw new NoResultException(sprintf('No message to consume into queue %s', $queue->getName()));
        }
        $message = $this->hydrate( new \stupidMessageQueue\Message(), $attributes);
        $message->setState(Message::STATE_RUNNING);
        $st = $this->getStatement( self::SQL_CONSUME );
        $result = $st->execute(
            array(
                ':id' => $message->getId(),
                ':queue' => $queue->getName(),
                ':state' => $message->getState(),
                ':consumer' => $consumer->getId(),
            )
        );
        if( $result == false ) {
            $this->con->rollBack();
            $this->treatError( $st );
        }
        $st->closeCursor();
        $this->con->commit();

        return $message;
    }

    protected function hydrate( $obj, array $attributes ) {
        foreach( $attributes as $key => $value ) {
            $setter = sprintf('set%s', ucfirst($key));
            if( method_exists($obj, $setter) ) {
                call_user_func(array($obj, $setter), $value);
            }
        }
        return $obj;
    }

    /**
     * @param \stupidMessageQueue\Queue\QueueInterface $queue
     * @param \stupidMessageQueue\Message\MessageInterface $message
     * @return \stupidMessageQueue\Message\MessageInterface
     */
    public function update( Queue $queue, Message $message )
    {
        $st = $this->getStatement( self::SQL_UPDATE );
        $result = $st->execute(
            array(
                ':id' => $message->getId(),
                ':content' => $message->getContent(),
                ':state' => $message->getState()
            )
        );
        if( $result == false ) {
            $this->treatError( $st );
        }
        $st->closeCursor();
        return $message;
    }

    /**
     * @return array
     */
    public function getConsumableQueue() {
        $st = $this->getStatement( self::SQL_CONSUMABLE_QUEUE );
        $result = $st->execute();
        if( $result == false ) {
            $this->treatError( $st );
        }

        $queues = array();
        while( $attribues = $st->fetch(PDO::FETCH_ASSOC) ) {
            $queues[] = new \stupidMessageQueue\Queue($attribues['queue']);
        }
        $st->closeCursor();
        return $queues;
    }
}
