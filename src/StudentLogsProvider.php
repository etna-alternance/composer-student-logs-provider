<?php

namespace ETNA\Silex\Provider\StudentLogs;

use Silex\Application;
use Silex\ServiceProviderInterface;

class StudentLogsProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        return $app;
    }

    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app["amqp.queues.options"] = array_merge($app["amqp.queues.options"], [
            "students_logs" => [
                "passive"     => false,
                "durable"     => true,
                "exclusive"   => false,
                "auto_delete" => false,
                "exchange"    => "default",
                "routing.key" => "students_logs",
                "channel"     => "default",
            ],
        ]);

        $app["student_logs"] = $app->share(
            function (Application $app) {
                return function($student_id, $session_id = null, $activity_id = null, $type, $duration, \DateTime $start, array $info_sup = []) use ($app) {
                    $job = array_merge(
                        $info_sup,
                        [
                            "student_id"  => $student_id,
                            "session_id"  => $session_id,
                            "activity_id" => $activity_id,
                            "type"        => $type,
                            "duration"    => $duration,
                            "start"       => $start->format("Y-m-d H:i:s"),
                        ]
                    );

                    $app["amqp.queues"]["students_logs"]->send($job);
                    $app["logs"]->notice("Got logs for student {$student_id} with duration {$duration}", $job);
                };
            }
        );
    }
}
