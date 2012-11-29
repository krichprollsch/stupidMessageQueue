<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  29/11/12 13:26
 */

namespace CoG\StupidMQ\Tests;

/**
 * BaseTest
 *
 * @author pierre
 */
abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    protected function getAdapterMock() {
        $adapter = $this->getMock(
            'CoG\\StupidMQ\\Adapter\\AdapterInterface',
            array('publish', 'consume')
        );

        return $adapter;
    }

    protected function getQueueMock( $opt = array() ) {
        $queue = $this->getMock(
            'CoG\\StupidMQ\\Queue\\QueueInterface',
            array('getName', 'publish', 'consume')
        );

        foreach( $opt as $key => $value ) {
            $queue->expects($this->any())
                ->method('get'.ucfirst($key))
                ->will($this->returnValue($value));
        }

        return $queue;
    }

    protected function getMessageMock( $opt = array() ) {
        $message = $this->getMock(
            'CoG\\StupidMQ\\Message\\MessageInterface',
            array(
                'getId', 'setId',
                'getContent', 'setContent',
                'getState', 'setState',
                'serialize', 'unserialize',
            )
        );

        foreach( $opt as $key => $value ) {
            $method = $key == 'serialize' ? 'serialize' : 'get'.ucfirst($key);
            $message->expects($this->any())
                ->method($method)
                ->will($this->returnValue($value));
        }

        return $message;
    }

    protected function getChannelMock() {
        $channel = $this->getMock(
            'CoG\\StupidMQ\\Channel\\ChannelInterface',
            array('publish', 'consume')
        );

        return $channel;
    }


}
