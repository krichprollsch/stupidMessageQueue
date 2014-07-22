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
    protected function getAdapterMock( $opt = array() ) {
        $adapter = $this->getMock(
            'CoG\\StupidMQ\\Adapter\\AdapterInterface',
            array('publish', 'consume', 'get', 'feedback', 'findAll', 'findByInterval')
        );
        $this->affectReturnsToMock( $adapter, $opt );

        return $adapter;
    }

    protected function getQueueMock( $opt = array() ) {
        $queue = $this->getMock(
            'CoG\\StupidMQ\\Queue\\QueueInterface',
            array('getName', 'publish', 'consume', 'get', 'feedback', 'findAll', 'findByInterval')
        );
        $this->affectReturnsToMock( $queue, $opt );

        return $queue;
    }

    protected function getMessageMock( $opt = array() ) {
        $message = $this->getMock(
            'CoG\\StupidMQ\\Message\\MessageInterface',
            array(
                'getId', 'setId',
                'getContent', 'setContent',
                'getState', 'setState',
                'getFeedback', 'setFeedback',
                'serialize', 'unserialize',
                'getCreated_at', 'setCreated_at',
                'getUpdated_at', 'setUpdated_at'
            )
        );
        $this->affectReturnsToMock( $message, $opt );

        return $message;
    }

    protected function getChannelMock($opt = array() ) {
        $channel = $this->getMock(
            'CoG\\StupidMQ\\Channel\\ChannelInterface',
            array('publish', 'consume', 'get', 'feedback', 'findAll', 'findByInterval')
        );
        $this->affectReturnsToMock( $channel, $opt );

        return $channel;
    }

    protected function affectReturnsToMock( $mock, $returns = array(), $getter=true ) {
        foreach( $returns as $key => $value ) {
            $method = $getter ? 'get'.ucfirst($key) : $key;
            $mock->expects($this->any())
                ->method($method)
                ->will($this->returnValue($value));
        }
    }


}
