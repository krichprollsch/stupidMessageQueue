<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  29/11/12 13:33
 */

namespace CoG\StupidMQ\Tests\Adapter;

use CoG\StupidMQ\Adapter\AdapterPdoMysql;
use CoG\StupidMQ\Tests\BaseTest;
use CoG\StupidMQ\Message\MessageInterface;

/**
 * AdapterFileTest
 *
 * @author pierre
 */
class AdapterPdoMysqlTest extends BaseTest
{
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

    protected function getStatementMock( $opt=array() ) {
        $st = $this->getMock(
            '\PDOStatement',
            array('execute', 'prepare')
        );

        $st->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(true));

        return $st;
    }

    protected function getPdoMock( $opt=array() ) {
        $pdo = $this->getMock(
            'CoG\\StupidMQ\\Tests\\Adapter\\PDOTestable',
            array('execute', 'prepare', 'lastInsertId')
        );

        $pdo->expects($this->any())
            ->method('prepare')
            ->will($this->returnValue($this->getStatementMock()));

        foreach( $opt as $key => $value ) {
            $pdo->expects($this->any())
                ->method('get'.ucfirst($key))
                ->will($this->returnValue($value));
        }

        return $pdo;
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

        $pdo = $this->getPdoMock();

        $adapter = new AdapterPdoMysql( $pdo );
        $adapter->publish( $queue, $message );

    }


    public function testConsume() {
        $this->markTestIncomplete('Consume test must be set');
    }

    public function testGet() {
        $this->markTestIncomplete('Get test must be set');
    }
}

class PDOTestable extends \PDO
{
    public function __construct(){}
}
