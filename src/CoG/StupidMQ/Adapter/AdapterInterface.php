<?php

namespace CoG\StupidMQ\Adapter;

use CoG\StupidMQ\Queue\QueueInterface;
use CoG\StupidMQ\Message\MessageInterface;

/**
 * User: pierre
 *
 */
interface AdapterInterface
{

    /**
     * @param QueueInterface $queue
     * @param MessageInterface $message
     * @return MessageInterface
     */
    public function publish( QueueInterface $queue, MessageInterface $message );

    /**
     * @param QueueInterface $queue
     * @param MessageInterface $message
     * @return MessageInterface
     */
    public function consume( QueueInterface $queue, MessageInterface $message );

}
