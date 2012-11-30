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
            array_keys(
                array_merge(
                    array(
                        'execute' => null,
                        'prepare' => null
                    ),
                    $opt
                )
            )
        );
        $this->affectReturnsToMock( $st, $opt, false );

        return $st;
    }

    protected function getPdoMock( $opt=array() ) {
        $pdo = $this->getMock(
            'CoG\\StupidMQ\\Tests\\Adapter\\PDOTestable',
            array_keys(
                array_merge(
                    array(
                        'execute' => null,
                        'prepare' => null,
                        'lastInsertId' => null,
                        'beginTransaction' => null,
                        'commit' => null,
                        'rollback' => null
                    ),
                    $opt
                )
            )
        );
        $this->affectReturnsToMock( $pdo, $opt, false );

        return $pdo;
    }

    /**
     * @dataProvider provider
     */
    public function testPublish( $content ) {
        $queue = $this->getQueueMock(array('name' => uniqid()));
        $message = $this->getMessageMock(array(
            'content' => $content,
            'state' => MessageInterface::STATE_NEW
        ));

        $st = $this->getStatementMock();
        $st->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(true));

        $pdo = $this->getPdoMock(array('prepare' => $st));

        $adapter = new AdapterPdoMysql( $pdo );
        $adapter->publish( $queue, $message );

    }


    /**
     * @dataProvider provider
     */
    public function testConsume( $content ) {
        $queue = $this->getQueueMock(array('name' => uniqid()));
        $message_expected = $this->getMessageMock(array(
            'content' => $content
        ));

        $st = $this->getStatementMock(array(
            'execute' => true,
            'fetch' => array('content' => $content),
            'rowCount' => 1
        ));

        $pdo = $this->getPdoMock();
        $pdo->expects($this->exactly(2))
            ->method('prepare')
            ->will($this->returnValue($st));

        $message = $this->getMessageMock();
        $adapter = new AdapterPdoMysql( $pdo );
        $message = $adapter->consume( $queue, $message );

        $this->assertEquals( $message_expected, $message );
    }

    /**
     * @expectedException \CoG\StupidMQ\Exception\NoResultException
     */
    public function testConsumeEmpty() {
        $queue = $this->getQueueMock(array('name' => uniqid()));
        $st = $this->getStatementMock(array(
            'execute' => true,
            'rowCount' => 0
        ));

        $pdo = $this->getPdoMock(array('prepare' => $st));

        $adapter = new AdapterPdoMysql( $pdo );
        $adapter->consume( $queue, $this->getMessageMock() );
    }

    public function testGet() {
        $this->markTestIncomplete('Get test must be set');
    }
}

class PDOTestable extends \PDO
{
    public function __construct(){}
}
