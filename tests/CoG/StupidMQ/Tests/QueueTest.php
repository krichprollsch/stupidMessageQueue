<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  29/11/12 13:14
 */

namespace CoG\StupidMQ\Tests;

use CoG\StupidMQ\Queue;

/**
 * QueueTest
 *
 * @author pierre
 */
class QueueTest extends BaseTest
{

    public function contentProvider()
    {
        return array(
            array('this is a content'),
            array(json_encode( array('message' => 'this is a json'))),
            array(serialize( array('message' => 'this is a json'))),
            array(null),
            array(''),
            array(123564)
        );
    }

    /**
     * @dataProvider contentProvider
     */
    public function testPublish( $content ) {

        $message_expected = $this->getMessageMock(array('content' => $content));

        $channel = $this->getChannelMock();
        $channel->expects($this->once())
            ->method('publish')
            ->will( $this->returnValue( $message_expected ));

        $queue = new Queue( $channel, 'this is a test' );
        $message = $queue->publish($content);

        $this->assertEquals( $content, $message->getContent() );

    }

    /**
     * @dataProvider contentProvider
     */
    public function testConsume( $content ) {
        $message_expected = $this->getMessageMock(array('content' => $content));

        $channel = $this->getChannelMock();
        $channel->expects($this->once())
            ->method('consume')
            ->will($this->returnValue($message_expected));

        $queue = $this->getQueueMock();

        $queue = new Queue( $channel, 'this is a test' );
        $message = $queue->consume();

        $this->assertEquals( $content, $message->getContent() );
    }


}