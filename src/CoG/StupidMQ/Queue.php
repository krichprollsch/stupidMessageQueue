<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  29/11/12 11:18
 */

namespace CoG\StupidMQ;

use CoG\StupidMQ\Queue\QueueInterface;
use CoG\StupidMQ\Channel\ChannelInterface;

/**
 * Queue
 *
 * @author pierre
 */
class Queue implements QueueInterface
{
    protected $channel;

    protected $name;

    public function __construct(ChannelInterface $channel, $name)
    {
        $this->channel = $channel;
        $this->name = $name;
    }

    /**
     * @param string $content
     * @return Message\MessageInterface
     */
    public function publish($content)
    {
        return $this->channel->publish($this, $content);
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return MessageInterface
     */
    public function consume()
    {
        return $this->channel->consume($this);
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        return $this->channel->get($this, $id);
    }

    /**
     * @inheritdoc
     */
    public function feedback($id, $state, $feedback)
    {
        return $this->channel->feedback($this, $id, $state, $feedback);
    }

    /**
     * @param int $state
     * @return array
     */
    public function findAll(array $ids)
    {
        return $this->channel->findAll($this, $ids);
    }
}
