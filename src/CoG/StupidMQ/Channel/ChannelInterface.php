<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  29/11/12 10:42
 */

namespace CoG\StupidMQ\Channel;

use CoG\StupidMQ\Queue\QueueInterface;
use CoG\StupidMQ\Message\MessageInterface;
use CoG\StupidMQ\Exception\NoResultException;
use CoG\StupidMQ\Exception\NotFoundException;

/**
 * ChannelInterface
 *
 * @author pierre
 */
interface ChannelInterface
{

    /**
     * @param QueueInterface $queue
     * @param string $content
     * @return MessageInterface
     */
    public function publish(QueueInterface $queue, $content);

    /**
     * @param QueueInterface $queue
     * @return MessageInterface
     * @throw NoResultException
     */
    public function consume(QueueInterface $queue);

    /**
     * @param QueueInterface $queue
     * @param $id message id
     * @return MessageInterface
     * @throw NotFoundException
     */
    public function get(QueueInterface $queue, $id);

    /**
     * @param QueueInterface $queue
     * @param int $id
     * @param string $state
     * @param string $feedback
     * @return MessageInterface
     * @throw NotFoundException
     */
    public function feedback(QueueInterface $queue, $id, $state, $feedback);

    /**
     * @param QueueInterface $queue
     * @param null $state
     * @return array
     */
    public function findAll(QueueInterface $queue, $state = null);

}
