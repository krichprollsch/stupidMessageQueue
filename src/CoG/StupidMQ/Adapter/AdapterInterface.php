<?php

namespace CoG\StupidMQ\Adapter;

use CoG\StupidMQ\Queue\QueueInterface;
use CoG\StupidMQ\Message\MessageInterface;
use CoG\StupidMQ\Exception\NoResultException;
use CoG\StupidMQ\Exception\NotFoundException;

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
     * @param array $ids
     * @return array
     */
    public function findAll(QueueInterface $queue, MessageInterface $message, array $ids);

    /**
     * @param QueueInterface $queue
     * @param MessageInterface $message
     * @param int $first first entry.
     * @param int $limit limit number of entries.
     */
    public function findByInterval(QueueInterface $queue, MessageInterface $message, $first, $limit);
}
