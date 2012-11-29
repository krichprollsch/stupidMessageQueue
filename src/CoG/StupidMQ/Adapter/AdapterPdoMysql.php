<?php

namespace CoG\StupidMQ\Adapter;

use CoG\StupidMQ\Adapter\AdapterInterface;
use CoG\StupidMQ\Queue\QueueInterface;
use CoG\StupidMQ\Message\MessageInterface;
use CoG\StupidMQ\Exception\NoResultException;
use CoG\StupidMQ\Exception\RuntimeException;
use CoG\StupidMQ\Exception\InvalidArgumentException;
use CoG\StupidMQ\Message;

use PDO as PDO;

/**
 * User: pierre
 *
 */
class AdapterPdoMysql implements AdapterInterface
{

    const DEFAULT_TABLE_NAME = 'stupid_message_queue';

    const SQL_CREATE_TABLE = <<<EOF
CREATE TABLE IF NOT EXISTS `%s` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `state` varchar(8) DEFAULT '%s',
  `created_at` TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`),
  KEY `queue` (`queue`,`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOF;


    const SQL_INSERT = 'INSERT INTO %s (queue, content, state, created_at, updated_at) VALUES (:queue, :content, :state, NOW(), NOW())';
    const SQL_CONSUME_LOAD = 'SELECT * FROM %s WHERE queue=:queue AND state=:pending ORDER BY created_at ASC LIMIT 1';
    const SQL_CONSUME = 'UPDATE %s state=:state, updated_at=NOW() WHERE queue=:queue AND state=:pending AND id=:id';

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

    public function publish(QueueInterface $queue, MessageInterface $message)
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

    public function consume(QueueInterface $queue, MessageInterface $message)
    {
        $this->con->beginTransaction();
        $st = $this->getStatement( self::SQL_CONSUME_LOAD );
        $result = $st->execute(
            array(
                ':queue' => $queue->getName(),
                ':pending' => Message::STATE_PENDING
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
        $this->hydrate( $message, $attributes);
        $message->setState(Message::STATE_RUNNING);
        $st = $this->getStatement( self::SQL_CONSUME );
        $result = $st->execute(
            array(
                ':id' => $message->getId(),
                ':queue' => $queue->getName(),
                ':state' => $message->getState(),
                ':pending' => Message::STATE_PENDING
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
}