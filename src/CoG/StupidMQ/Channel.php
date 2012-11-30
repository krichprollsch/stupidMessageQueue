<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  29/11/12 11:15
 */

namespace CoG\StupidMQ;

use CoG\StupidMQ\Channel\ChannelInterface;
use CoG\StupidMQ\Adapter\AdapterInterface;
use CoG\StupidMQ\Queue\QueueInterface;
use CoG\StupidMQ\Message\MessageInterface;
use CoG\StupidMQ\Message;
use CoG\StupidMQ\Exception\NoResultException;
use CoG\StupidMQ\Exception\NotFoundException;

/**
 * Channel
 *
 * @author pierre
 */
class Channel implements ChannelInterface
{

    /**
     * @var Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * @param Adapter\AdapterInterface $adapter
     */
    public function __construct( AdapterInterface $adapter ) {
        $this->adapter = $adapter;
    }

    public function publish( QueueInterface $queue, $content ) {
        $message = new Message( $content );
        $this->adapter->publish( $queue, $message );
        return $message;
    }

    /**
     * @param QueueInterface $queue
     * @return MessageInterface
     */
    public function consume( QueueInterface $queue ) {
        return $this->adapter->consume( $queue, new Message());
    }

    /**
     * @param QueueInterface $queue
     * @param $id message id
     * @return MessageInterface
     * @throw NotFoundException
     */
    public function get(QueueInterface $queue, $id) {
        $message = new Message();
        $message->setId($id);
        return $this->adapter->get( $queue, $message);
    }
}
