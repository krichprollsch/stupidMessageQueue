<?php

namespace stupidMessageQueue\Store;

use PDO as PDO;

/**
 * User: pierre
 *
 */
class StorePdo implements StoreInterface
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOF;


    const SQL_INSERT_MESSAGE = 'INSERT INTO %s (queue, content, state, created_at, updated_at) VALUES (:queue, :content, :state, NOW(), NOW())';
    const SQL_CONSUME = 'UPDATE %s set consumer=:consumer, state=:state, updated_at=NOW() WHERE queue=:queue AND consumer IS NULL ORDER BY created_at ASC LIMIT 1';
    const SQL_LOAD = 'SELECT * FROM %s WHERE id=%d';

    protected $tablename;

    /**
     * @var PDO
     */
    protected $con;

    public function __construct( PDO $con, $tablename=self::DEFAULT_TABLE_NAME ) {
        $this->con = $con;
        $this->tablename = $tablename;
    }

    public function getTable() {
        return $this->tablename;
    }

    /**
     * @param \stupidMessageQueue\Queue\QueueInterface $queue
     * @param \stupidMessageQueue\Message\MessageInterface $message
     * @return \stupidMessageQueue\Message\MessageInterface
     */
    public function save(Queue $queue, Message $message)
    {

    }

    /**
     * @param string $id
     * @return \stupidMessageQueue\Message\MessageInterface
     */
    public function load(Queue $queue, Message $message)
    {
        // TODO: Implement load() method.
    }

    /**
     * @param \stupidMessageQueue\Queue\QueueInterface $queue
     * @return \stupidMessageQueue\Message\MessageInterface
     */
    public function consume(Consumer $consumer, Queue $queue)
    {
        // TODO: Implement consume() method.
    }


}
