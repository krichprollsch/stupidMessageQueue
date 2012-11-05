<?php

namespace stupidMessageQueue\Store;

use stupidMessageQueue\Message\MessageInterface as Message;
use stupidMessageQueue\Queue\QueueInterface as Queue;
use stupidMessageQueue\Consumer\ConsumerInterface as Consumer;

/**
 * User: pierre
 *
 */
interface StoreInterface
{

    /**
     * @param \stupidMessageQueue\Queue\QueueInterface $queue
     * @param \stupidMessageQueue\Message\MessageInterface $message
     * @return \stupidMessageQueue\Message\MessageInterface
     */
    public function save( Queue $queue, Message $message );

    /**
     * @param string $id
     * @return \stupidMessageQueue\Message\MessageInterface
     */
    public function load( Queue $queue, Message $message );

    /**
     * @param \stupidMessageQueue\Queue\QueueInterface $queue
     * @return \stupidMessageQueue\Message\MessageInterface
     */
    public function consume( Consumer $consumer, Queue $queue );

}
