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
    public function publish(QueueInterface $queue, MessageInterface $message);

    /**
     * @param QueueInterface $queue
     * @param MessageInterface $message
     * @return MessageInterface
     * @throw NoResultException
     */
    public function consume(QueueInterface $queue, MessageInterface $message);

    /**
     * @param QueueInterface $queue
     * @param MessageInterface $message
     * @return MessageInterface
     * @throw NotFoundException
     */
    public function get(QueueInterface $queue, MessageInterface $message);

    /**
     * @param QueueInterface $queue
     * @param MessageInterface $message
     * @return MessageInterface
     * @throw NotFoundException
     */
    public function feedback(QueueInterface $queue, MessageInterface $message);

    /**
     * @param QueueInterface $queue
     * @param MessageInterface $message
     * @param null $state
     * @return array
     */
    public function findAll(QueueInterface $queue, MessageInterface $message, array $ids);

}
