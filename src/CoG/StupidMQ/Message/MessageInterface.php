<?php

namespace CoG\StupidMQ\Message;

/**
 * User: pierre
 *
 */
interface MessageInterface extends \Serializable
{
    const STATE_NEW = 'new';
    const STATE_PENDING = 'pending';
    const STATE_RUNNING = 'running';
    const STATE_DONE = 'done';
    const STATE_ERROR = 'error';
    const STATE_CANCELED = 'canceled';

    /**
     * message uniq id
     * @return string
     */
    public function getId();

    /**
     * @param $id string
     */
    public function setId($id);

    /**
     * message content
     * @return string
     */
    public function getContent();

    /**
     * @param $content string
     */
    public function setContent($content);

    /**
     * message current state
     * @return string
     */
    public function getState();

    /**
     * @param $state string
     */
    public function setState($state);

    /**
     * message current feedback
     * @return string
     */
    public function getFeedback();

    /**
     * @param $feedback string
     */
    public function setFeedback($feedback);

    /**
     * message created at date
     * @return date
     */
    public function getCreated_at();

    /**
     * message updated at date
     * @return date
     */
    public function getUpdated_at();

    /**
     * @param $created_at date
     */
    public function setCreated_at($created_at);

    /**
     * @param $updatedAt date
     */
    public function setUpdated_at($updated_at);

}
