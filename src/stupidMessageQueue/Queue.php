<?php

namespace stupidMessageQueue;

use stupidMessageQueue\Queue\QueueInterface;

/**
 * User: pierre
 *
 */
class Queue implements QueueInterface
{
    protected $name;

    public function __construct( $name ) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }
}
