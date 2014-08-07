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
    public function publish($content);

    /**
     * @return MessageInterface
     * @throw NoResultException
     */
    public function consume();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param $id message id
     * @return MessageInterface
     * @throw NotFoundException
     */
    public function get($id);

    /**
     * @param $id
     * @param $state string
     * @param $feedback string
     * @return MessageInterface
     * @throw NotFoundException
     */
    public function feedback($id, $state, $feedback);

    /**
     * @param int $ids
     * @return array
     */
    public function findAll(array $ids);

    /**
     * @param int $first first entry.
     * @param int $limit limit number of entries.
     */
    public function findByInterval($first, $limit);
}
