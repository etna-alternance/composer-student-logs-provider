<?php

namespace ETNA\Silex\Provider\StudentLogs;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Silex\Application;

/**
 *
 */
class StudentLogs
{
    public function __construct($app)
    {
        $this->producer = $app['rabbit.producer']['student_logs'];
        $this->logger   = $app['logs'];
    }

    public function addLogs($student_id, $type, $duration, $session_id = null, $activity_id = null, array $info_sup = [])
    {
        $now = new \DateTime(date('Y-m-d H:i:s'));
        $job = [
                'student_id'  => $student_id,
                'session_id'  => $session_id,
                'activity_id' => $activity_id,
                'type'        => $type,
                'duration'    => $duration,
                'start'       => $now->format('Y-m-d H:i:s'),
                'metas'       => $info_sup,
        ];

        $log_content = 'Got logs of type {$type} for student {$student_id} with duration {$duration}';
        $this->producer->publish(json_encode($job), 'student_logs');
        $this->logger->notice($log_content, $job);
    }

    public function getProducerConfig()
    {
        $student_logs_config = [
            'exchange' => [
                'name'        => 'etna',
                'channel'     => 'default',
                'type'        => 'direct',
                'passive'     => false,
                'durable'     => true,
                'auto_delete' => false,
            ]
            'queue' => [
                'passive'     => false,
                'durable'     => true,
                'exclusive'   => false,
                'auto_delete' => false,
                'exchange'    => 'etna',
                'routing.key' => 'students_logs',
                'channel'     => 'default'
            ]
        ];

        return [
            'student_logs' => [
                'connection'       => 'default',
                'exchange_options' => $student_logs_config['exchange'],
                'queue_options'    => $student_logs_config['queue']
            ]
        ];
    }
}
