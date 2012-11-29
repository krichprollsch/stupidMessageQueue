<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  29/11/12 10:45
 */

namespace CoG\StupidMQ\Queue;

use CoG\StupidMQ\Message\MessageInterface;

/**
 * QueueInterface
 *
 * @author pierre
 */
interface QueueInterface
{
    /**
     * @param string $content
     * @return MessageInterface
     */
    public function publish( $content );

    /**
     * @return MessageInterface
     */
    public function consume();

    /**
     * @return string
     */
    public function getName();
}
