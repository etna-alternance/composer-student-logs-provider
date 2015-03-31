<?php

namespace ETNA\Monolog\Handler\StudentLogs;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class StudentLogsHandler extends AbstractProcessingHandler
{
    protected $queue;

    public function __construct($queue = null, $level = Logger::DEBUG, $bubble = true)
    {
        $this->queue = $queue;
        if (null === $this->queue) {
            throw new \Exception("StudentLogsHandler requires a RabbitMQ queue");
        }

        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        //Cette fonction est appelée deux fois :
        // - la première fois uniquement avec le level
        // - la fois d'après avec ce qui nous interresse
        if (1 === count($record) && isset($record["level"])) {
            return true;
        }
        return isset($record["context"]["duration"]);
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        //Envoi d'un job a rabbitMQ pour la comptabilisation des logs
        if (!isset($record["context"])) {
            throw new \Exception("StudentLogsHandler need $record to have context datas");
        }
        $this->queue->send($record["context"]);
    }
}
