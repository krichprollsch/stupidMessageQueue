StupidMessageQueue
==================

Message Queue based on Symfony Components

Usage
-----

Providing messages :

  $adpater = new \Cog\StupiMQ\AdapterFile( sys_get_temp_dir() );
  $channel = new \Cog\StupiMQ\Channel( $adapter );
  $queue = new \Cog\StupiMQ\Queue( $channel, 'myQueue' );

  $queue->publish( 'hello world' );


Consuming messages :

  $adpater = new \Cog\StupiMQ\AdapterFile( sys_get_temp_dir() );
  $channel = new \Cog\StupiMQ\Channel( $adapter );
  $queue = new \Cog\StupiMQ\Queue( $channel, 'myQueue' );

  $message = $queue->consume();

Todo
----

- configure database indexes
- FileAdapter