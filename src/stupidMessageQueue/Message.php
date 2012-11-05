<?php

namespace stupidMessageQueue;

use stupidMessageQueue\Message\MessageInterface;

/**
 * User: pierre
 *
 */
class Message implements MessageInterface
{
    protected $id;
    protected $queue;
    protected $content;
    protected $state;

    public function __construct( $content ) {
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
     * @param $id string
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param $state string
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

        throw new \InvalidArgumentException('Bad value for state');
    }


}
