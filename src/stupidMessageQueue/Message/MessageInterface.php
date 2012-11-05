<?php

namespace stupidMessageQueue\Message;

/**
 * User: pierre
 *
 */
interface MessageInterface
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


}
