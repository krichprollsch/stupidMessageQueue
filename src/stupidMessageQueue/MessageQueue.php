<?php

namespace stupidMessageQueue;

use stupidMessageQueue\Message\MessageInterface as Message;
use stupidMessageQueue\Store\StoreInterface as Store;
use stupidMessageQueue\Queue\QueueInterface as Queue;
use stupidMessageQueue\Consumer\ConsumerInterface as Consumer;

/**
 * User: pierre
 *
 */
class MessageQueue
{

    /**
     * @var Store
     */
    protected $store;

    /**
     * @param Store\StoreInterface $store
     */
    public function __construct( Store $store ) {
        $this->store = $store;
    }

    /**
     * @param Queue\QueueInterface $queue
     * @param Message\MessageInterface $message
     */
    public function provide( Queue $queue, Message $message ) {
        return $this->store->save($queue, $message);
    }

    /**
     * @param Queue $queue
     * @return Message
     */
    public function consume( Consumer $consumer, Queue $queue ) {
        return $this->store->consume( $consumer, $queue);
    }

    /**
     * @param Queue\QueueInterface $queue
     * @param Message\MessageInterface $message
     */
    public function load( Queue $queue, Message $message ) {
        return $this->store->load($queue, $message);
    }

}
