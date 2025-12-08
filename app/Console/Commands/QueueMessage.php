<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class QueueMessage extends Command
{
    protected $signature = 'rabbit:produce {message}';

    protected $description = 'Produce a message to RabbitMQ';

    public function handle()
    {
        $queue = env('RABBITMQ_EXCHANGE_QUEUE');

        $msgBody = $this->argument('message');
        $connection = new AMQPStreamConnection(
            config('queue.connections.rabbitmq.host'),
            config('queue.connections.rabbitmq.port'),
            config('queue.connections.rabbitmq.login'),
            config('queue.connections.rabbitmq.password'),
            config('queue.connections.rabbitmq.vhost')
        );
        $channel = $connection->channel();
        $channel->queue_declare($queue, false, true, false, false);

        $msg = new AMQPMessage($msgBody, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $channel->basic_publish($msg, '', $queue);

        $channel->close();
        $connection->close();

        $this->info('Message published');
    }
}
