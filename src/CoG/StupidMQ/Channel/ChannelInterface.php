<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  29/11/12 10:42
 */

namespace CoG\StupidMQ\Channel;

use CoG\StupidMQ\Queue\QueueInterface;
use CoG\StupidMQ\Message\MessageInterface;

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
    public function publish( QueueInterface $queue, $content );

    /**
     * @param QueueInterface $queue
     * @return MessageInterface
     */
    public function consume( QueueInterface $queue );

}
