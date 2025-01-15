<?php declare(strict_types=1);

namespace App\Services\RabbitMQ;

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PhpAmqpLib\Connection\AMQPConnectionFactory;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService
{
    protected AbstractConnection $connection;

    public function __construct(public readonly array $config)
    {
        $connectionConfig = new AMQPConnectionConfig();
        $connectionConfig->setHost($config['host']);
        $connectionConfig->setPort((int) $config['port']);
        $connectionConfig->setUser($config['username']);
        $connectionConfig->setPassword($config['password']);
        $connectionConfig->setConnectionTimeout($config['connection']['connection_timeout']);
        $connectionConfig->setReadTimeout($config['connection']['read_write_timeout']);
        $connectionConfig->setWriteTimeout($config['connection']['read_write_timeout']);
        $connectionConfig->setHeartbeat($config['connection']['heartbeat']);
        $connectionConfig->setIsSecure($config['is_secure']);

        $this->connection = AMQPConnectionFactory::create($connectionConfig);
    }

    public function publish(array $message, string $routingKey): void
    {
        $this
            ->declareExchange()
            ->declareQueue()
            ->bindQueue($routingKey)
            ->publishMessage($routingKey, $message);
    }

    private function declareExchange(): self
    {
        $this->connection->channel()->exchange_declare(
            exchange: $this->config['exchange'],
            type: AMQPExchangeType::TOPIC,
            passive: false,
            durable: true,
            auto_delete: false
        );

        return $this;
    }

    private function declareQueue(): self
    {
        $this->connection->channel()->queue_declare(
            queue: $this->config['queue'],
            passive: false,
            durable: true,
            exclusive: false,
            auto_delete: false,
            nowait: false,
        );

        return $this;
    }

    private function bindQueue(string $routingKey): self
    {
        $this->connection->channel()->queue_bind(
            $this->config['queue'],
            $this->config['exchange'],
            $routingKey
        );

        return $this;
    }

    private function publishMessage(string $routingKey, array $message): void
    {
        $message = new AMQPMessage(
            json_encode($message),
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]
        );

        $this->connection->channel()->basic_publish($message, $this->config['exchange'], $routingKey);
    }
}
