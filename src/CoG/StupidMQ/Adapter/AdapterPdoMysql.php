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
  `feedback` text DEFAULT NULL,
  `state` varchar(8) DEFAULT '%s',
  `created_at` TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`),
  KEY `queue` (`queue`,`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOF;


    const SQL_INSERT = 'INSERT INTO %s (queue, content, state, created_at, updated_at) VALUES (:queue, :content, :state, NOW(), NOW())';
    const SQL_CONSUME_LOAD = 'SELECT * FROM %s WHERE queue=:queue AND state=:pending ORDER BY created_at ASC LIMIT 1';
    const SQL_CONSUME = 'UPDATE %s SET state=:state, updated_at=NOW() WHERE queue=:queue AND state=:pending AND id=:id';
    const SQL_LOAD = 'SELECT * FROM %s WHERE id=:id AND queue=:queue';
    const SQL_FEEDBACK = 'UPDATE %s SET state=:state, feedback=:feedback, updated_at=NOW() WHERE id=:id';
    const SQL_FIND = 'SELECT * FROM %s WHERE id IN (%s) AND queue=:queue';

    protected $tablename;

    protected $statements;

    /**
     * @var PDO
     */
    protected $con;

    public function __construct(PDO $con, $tablename = self::DEFAULT_TABLE_NAME)
    {
        $this->con = $con;
        $this->tablename = $tablename;
        $this->statements = array();
    }

    /**
     * @param $sql
     * @param mixed $extra
     * @return mixed
     */
    protected function getStatement($sql, $extra = null)
    {
        if (!isset($this->statements[$sql])) {
            $st = $this->con->prepare(
                sprintf($sql, $this->getTable(), $extra)
            );
            $this->statements[$sql] = $st;
        }

        return $this->statements[$sql];
    }

    public function getTable()
    {
        return $this->tablename;
    }

    protected function treatError(\PDOStatement $st)
    {
        $info = $st->errorInfo();
        throw new RuntimeException(sprintf('Error %s while running sql : %s (%s)', $info[0], $info[2], $info[1]));
    }

    public function publish(QueueInterface $queue, MessageInterface $message)
    {
        if ($message->getState() != Message::STATE_NEW) {
            throw new InvalidArgumentException('Message is not in new state');
        }
        $message->setState(Message::STATE_PENDING);
        $st = $this->getStatement(self::SQL_INSERT);
        $result = $st->execute(
            array(
                ':queue' => $queue->getName(),
                ':content' => $message->getContent(),
                ':state' => $message->getState()
            )
        );
        if (!$result) {
            $this->treatError($st);
        }
        $this->hydrate($message, array('id' => $this->con->lastInsertId()));
        $st->closeCursor();

        return $message;
    }

    public function consume(QueueInterface $queue, MessageInterface $message)
    {
        $this->con->beginTransaction();
        $st = $this->getStatement(self::SQL_CONSUME_LOAD);
        $result = $st->execute(
            array(
                ':queue' => $queue->getName(),
                ':pending' => Message::STATE_PENDING
            )
        );
        if (!$result) {
            $this->con->rollBack();
            $this->treatError($st);
        }
        $attributes = $st->fetch(PDO::FETCH_ASSOC);
        $st->closeCursor();

        if ($st->rowCount() <= 0) {
            $this->con->rollBack();
            throw new NoResultException(sprintf('No message to consume into queue %s', $queue->getName()));
        }
        $this->hydrate($message, $attributes);
        $message->setState(Message::STATE_RUNNING);
        $st = $this->getStatement(self::SQL_CONSUME);
        $result = $st->execute(
            array(
                ':id' => $message->getId(),
                ':queue' => $queue->getName(),
                ':state' => $message->getState(),
                ':pending' => Message::STATE_PENDING
            )
        );
        if (!$result) {
            $this->con->rollBack();
            $this->treatError($st);
        }
        if ($st->rowCount() != 1) {
            $this->con->rollBack();
            throw new RuntimeException(sprintf('The message has been used by another consumer'));
        }

        $st->closeCursor();
        $this->con->commit();

        return $message;
    }

    protected function hydrate($obj, array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $setter = sprintf('set%s', ucfirst($key));
            if (method_exists($obj, $setter)) {
                call_user_func(array($obj, $setter), $value);
            }
        }
        return $obj;
    }

    public function get(QueueInterface $queue, MessageInterface $message)
    {
        $st = $this->getStatement(self::SQL_LOAD);
        $result = $st->execute(
            array(
                ':id' => $message->getId(),
                ':queue' => $queue->getName(),
            )
        );
        if (!$result) {
            $this->treatError($st);
        }
        $attributes = $st->fetch(PDO::FETCH_ASSOC);
        $st->closeCursor();

        if ($st->rowCount() <= 0) {
            throw new NotFoundException(sprintf('No message found for %d id', $message->getId()));
        }
        return $this->hydrate($message, $attributes);
    }

    public function feedback(QueueInterface $queue, MessageInterface $message)
    {
        $st = $this->getStatement(self::SQL_FEEDBACK);
        $result = $st->execute(
            array(
                ':feedback' => $message->getFeedback(),
                ':state' => $message->getState(),
                ':id' => $message->getId(),
            )
        );
        if (!$result) {
            $this->treatError($st);
        }
        $st->closeCursor();

        return $message;
    }

    public function findAll(QueueInterface $queue, MessageInterface $message, array $ids)
    {
        $st = $this->getStatement(
            self::SQL_FIND,
            implode(',', $this->quote($ids))
        );
        $result = $st->execute(
            array(
                ':queue' => $queue->getName()
            )
        );
        if (!$result) {
            $this->treatError($st);
        }

        $messages = array();
        while ($attributes = $st->fetch(PDO::FETCH_ASSOC)) {
            $messages[] = $this->hydrate(clone $message, $attributes);
        }

        $st->closeCursor();

        return $messages;
    }

    public function quote(array $params)
    {
        foreach ($params as &$val) {
            $val=$this->con->quote($val);
        }
        return $params;
    }
}
