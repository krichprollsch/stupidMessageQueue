<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  05/11/12 14:34
 */

namespace stupidMessageQueue;

use stupidMessageQueue\Consumer\ConsumerInterface;

/**
 * Consumer
 *
 * @author pierre
 */
class Consumer implements ConsumerInterface
{

    protected $id;

    public function __construct( $id=null )
    {
        $this->id = $id == null ? uniqid('consumer_') : $id;
    }

    public function getId()
    {
        return $this->id;
    }

}
