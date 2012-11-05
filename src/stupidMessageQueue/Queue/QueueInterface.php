<?php

namespace stupidMessageQueue\Queue;

/**
 * User: pierre
 *
 */
interface QueueInterface
{
    /**
     * @return string
     */
    public function getName();
}
