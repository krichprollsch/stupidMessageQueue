StupidMessageQueue
==================

Message Queue based on Symfony Components

Usage
-----

Be carefull : FileAdapter is not yet working, please use AdapterPdoMysql instead.

Providing messages :

```php
$adpater = new \Cog\StupiMQ\AdapterFile( sys_get_temp_dir() );
$channel = new \Cog\StupiMQ\Channel( $adapter );
$queue = new \Cog\StupiMQ\Queue( $channel, 'myQueue' );

$queue->publish( 'hello world' );
```


Consuming messages :

```php
$adpater = new \Cog\StupiMQ\AdapterFile( sys_get_temp_dir() );
$channel = new \Cog\StupiMQ\Channel( $adapter );
$queue = new \Cog\StupiMQ\Queue( $channel, 'myQueue' );

$message = $queue->consume();
```

Giving feedback

```php
$adpater = new \Cog\StupiMQ\AdapterFile( sys_get_temp_dir() );
$channel = new \Cog\StupiMQ\Channel( $adapter );
$queue = new \Cog\StupiMQ\Queue( $channel, 'myQueue' );

$message = $queue->consume();
$queue->feedback(
    $message-getId(),
    CoG\StupidMQ\Message\MessageInterface::STATE_DONE),
    'my feedback'
);
```

Todo
----

- Terminate FileAdapter