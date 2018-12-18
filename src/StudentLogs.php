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
        $this->producer = $app['rabbit.producer']['students_logs'];
        $this->logger   = $app['logs'];
    }

    /**
     * Create a job to add logs to student.
     *
     * @param integer $student_id
     * @param string $type
     * @param integer $duration
     * @param mixed $session_id
     * @param mixed $activity_id
     * @param array $info_sup
     * @param mixed $start_date
     * @return void
     */
    public function addLogs(
        $student_id,
        $type,
        $duration,
        $session_id = null,
        $activity_id = null,
        array $info_sup = [],
        $start_date = null
    )
    {
        $start = $start_date ?: date('Y-m-d H:i:s');

        $job = [
                'student_id'  => $student_id,
                'session_id'  => $session_id,
                'activity_id' => $activity_id,
                'type'        => $type,
                'duration'    => $duration,
                'start'       => $start,
                'metas'       => $info_sup,
        ];

        $log_content = 'Got logs of type {$type} for student {$student_id} with duration {$duration}';
        $this->producer->publish(json_encode($job), 'students_logs');
        $this->logger->notice($log_content, $job);
    }

    public static function getProducerConfig()
    {
        $student_logs_exchange = [
            'name'        => 'etna',
            'channel'     => 'default',
            'type'        => 'direct',
            'passive'     => false,
            'durable'     => true,
            'auto_delete' => false,
        ];

        return [
            'students_logs' => [
                'connection'       => 'default',
                'exchange_options' => $student_logs_exchange,
                'queue_options'    => ['name' => 'students_logs', 'routing_keys' => ['students_logs']]
            ]
        ];
    }
}
