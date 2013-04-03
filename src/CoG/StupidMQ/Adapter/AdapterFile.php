<?php

namespace CoG\StupidMQ\Adapter;

use CoG\StupidMQ\Adapter\AdapterInterface;
use CoG\StupidMQ\Queue\QueueInterface;
use CoG\StupidMQ\Message\MessageInterface;
use CoG\StupidMQ\Exception\NoResultException;
use CoG\StupidMQ\Exception\RuntimeException;
use CoG\StupidMQ\Exception\InvalidArgumentException;

use PDO as PDO;

/**
 * User: pierre
 *
 */
class AdapterFile implements AdapterInterface
{

    protected $directory;

    public function __construct( $directory ) {
        $this->setDirectory($directory);
    }

    protected function setDirectory( $directory ) {
        if( !is_dir($directory)) {
            throw new InvalidArgumentException(sprintf('%s is not a dir', $directory));
        }

        if( !is_readable($directory)) {
            throw new InvalidArgumentException(sprintf('%s is not a readable dir', $directory));
        }

        if( !is_writable($directory)) {
            throw new InvalidArgumentException(sprintf('%s is not a writable dir', $directory));
        }

        $this->directory = $directory;
    }

    public function publish(QueueInterface $queue, MessageInterface $message)
    {
        if( $message->getState() != MessageInterface::STATE_NEW ) {
            throw new InvalidArgumentException('Message is not in new state');
        }
        $message->setState( MessageInterface::STATE_PENDING );

        $hdle = $this->open($queue, "a");
        $this->write($hdle, $message);
        $this->close($hdle);

        return $message;
    }

    public function consume( QueueInterface $queue, MessageInterface $message ) {
        //TODO
    }

    public function get(QueueInterface $queue, MessageInterface $message) {
        //TODO
    }

    public function feedback(QueueInterface $queue, MessageInterface $message) {
        //TODO
    }

    public function findAll(QueueInterface $queue, MessageInterface $message, $state = null) {
        //TODO
    }

    protected function serialize( MessageInterface $message ) {
        return base64_encode(serialize($message));
    }

    protected function write( $hdle, MessageInterface $message ) {
        if( ($id = ftell($hdle)) === false ) {
            $error = error_get_last();
            throw new RuntimeException(sprintf( 'Unable to get position : %s', $error['message']));
        }
        $message->setId( $id );
        $content = $this->serialize($message);
        $content .= $id == 0 ? null : "\n";
        if( !(fwrite( $hdle, $content))) {
            $error = error_get_last();
            throw new RuntimeException(sprintf( 'Unable to write : %s', $error['message']));
        }
    }

    protected function open( QueueInterface $queue, $mode="w" ) {
        $filename = $this->getFilename( $queue );
        if(!($hdle = fopen($filename, $mode))) {
            $error = error_get_last();
            throw new RuntimeException(sprintf( 'Unable to open %s : %s', $filename, $error['message']));
        }
        return $hdle;
    }

    protected function close( $hdle ) {
        if(!(fclose($hdle))) {
            $error = error_get_last();
            throw new RuntimeException(sprintf( 'Unable to close : %s', $error['message']));
        }
    }

    protected function getFilename( QueueInterface $queue ) {
        return $this->getDirectory() . '/' . base64_encode( $queue->getName() );
    }

    protected function getDirectory() {
        return $this->directory;
    }

}
