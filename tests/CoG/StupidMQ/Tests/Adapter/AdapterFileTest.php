<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  29/11/12 13:33
 */

namespace CoG\StupidMQ\Tests\Adapter;

use CoG\StupidMQ\Adapter\AdapterFile;
use CoG\StupidMQ\Tests\BaseTest;
use CoG\StupidMQ\Message\MessageInterface;

/**
 * AdapterFileTest
 *
 * @author pierre
 */
class AdapterFileTest extends BaseTest
{
    protected $dir;

    public function setUp() {
        $this->dir = sys_get_temp_dir().'/'.uniqid('AdapterFileTest_');
        mkdir($this->dir);
    }

    public function tearDown() {
        if(($files = scandir($this->dir))) {
            foreach( $files as $file ) {
                @unlink( $this->dir.'/'.$file);
            }
        }
        @rmdir($this->dir);
    }

    public function provider() {
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
     * @dataProvider provider
     */
    public function testPublish( $content ) {
        $queue = $this->getQueueMock(array('name' => uniqid()));
        $message = $this->getMessageMock(array(
            'content' => $content,
            'state' => MessageInterface::STATE_NEW,
            'serialize' => serialize($content)
        ));

        $adapter = new AdapterTestable( $this->dir );
        $adapter->publish( $queue, $message );

        $this->assertFileExists($adapter->getFilename($queue));
        $this->assertStringEqualsFile( $adapter->getFilename($queue), $adapter->serialize($message));
    }

    public function testConsume() {
        $this->markTestIncomplete('Consume test must be set');
    }

    public function testGet() {
        $this->markTestIncomplete('Get test must be set');
    }

    public function testFeedback() {
        $this->markTestIncomplete('Feedback test must be set');
    }
}

class AdapterTestable extends AdapterFile {
    public function getFilename( $queue ) {
        return parent::getFilename($queue);
    }
    public function serialize($message) {
        return parent::serialize($message);
    }
}
