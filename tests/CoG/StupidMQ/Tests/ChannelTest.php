<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  29/11/12 12:39
 */

namespace CoG\StupidMQ\Tests;

use CoG\StupidMQ\Channel;
use CoG\StupidMQ\Message\MessageInterface;

/**
 * ChannelTest
 *
 * @author pierre
 */
class ChannelTest extends BaseTest
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
        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('publish');

        $queue = $this->getQueueMock();

        $channel = new Channel( $adapter );
        $message = $channel->publish($queue, $content);

        $this->assertEquals( $content, $message->getContent() );

    }

    /**
     * @dataProvider contentProvider
     */
    public function testConsume( $content ) {
        $message_expected = $this->getMessageMock(array('content' => $content));

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('consume')
            ->will($this->returnValue($message_expected));

        $queue = $this->getQueueMock();

        $channel = new Channel( $adapter );
        $message = $channel->consume($queue);

        $this->assertEquals( $message_expected, $message );
    }

    /**
     * @dataProvider contentProvider
     */
    public function testGet( $content ) {
        $id = uniqid();
        $message_expected = $this->getMessageMock(array('content' => $content, 'id' => $id));

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($message_expected));

        $queue = $this->getQueueMock();

        $channel = new Channel( $adapter );
        $message = $channel->get($queue, $id);

        $this->assertEquals( $message_expected, $message );
    }

    /**
     * @dataProvider contentProvider
     */
    public function testFeedback( $content ) {
        $id = uniqid();
        $message_expected = $this->getMessageMock(array('content' => $content, 'id' => $id));

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('feedback')
            ->will($this->returnValue($message_expected));

        $queue = $this->getQueueMock();

        $channel = new Channel( $adapter );
        $message = $channel->feedback($queue, $id, 'done', null);

        $this->assertEquals( $message_expected, $message );
    }

}
