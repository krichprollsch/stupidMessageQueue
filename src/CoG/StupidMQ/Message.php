<?php

namespace CoG\StupidMQ;

use CoG\StupidMQ\Message\MessageInterface;
use CoG\StupidMQ\Exception\UnexpectedValueException;

/**
 * User: pierre
 *
 */
class Message implements MessageInterface
{
    protected $id;
    protected $content;
    protected $state;
    protected $feedback;
    protected $created_at;
    protected $updated_at;

    public function __construct( $content=null ) {
        $this->setContent($content);
        $this->state = self::STATE_NEW;
    }

    public function setContent( $content ) {
        $this->content = $content;
    }

    /**
     * message uniq id
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * message content
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * message current state
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * message created at date
     * @return date
     */
    public function getCreated_at()
    {
        return $this->created_at;
    }

    /**
     * message updated at date
     * @return date
     */
    public function getUpdated_at()
    {
        return $this->updated_at;
    }

    /**
     * @param $created_at date
     */
    public function setCreated_at($created_at)
    {
        $this->created_at = $created_at;
    }

    /**
     * @param $updatedAt date
     */
    public function setUpdated_at($updated_at)
    {
        $this->updated_at = $updated_at;
    }

    /**
     * @param $id string
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param $state string
     * @throws Exception\UnexpectedValueException
     * @return void
     */
    public function setState($state)
    {
        switch($state) {
            case self::STATE_NEW:
            case self::STATE_CANCELED:
            case self::STATE_DONE:
            case self::STATE_ERROR:
            case self::STATE_PENDING:
            case self::STATE_RUNNING:
                $this->state = $state;
                return;
        }

        throw new UnexpectedValueException('Bad value for state');
    }

    public function serialize() {
        return serialize($this);
    }

    public function unserialize($string) {
        return unserialize($string);
    }

    public function setFeedback( $feedback ) {
        $this->feedback = $feedback;
    }

    /**
     * message content
     * @return string
     */
    public function getFeedback()
    {
        return $this->feedback;
    }
}
