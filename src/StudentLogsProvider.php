<?php

namespace ETNA\Silex\Provider\StudentLogs;

use ETNA\Monolog\Handler\StudentLogsHandler;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\SyslogUdpHandler;

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
        $app['amqp.queues.options'] = array_merge($app['amqp.queues.options'], [
            'students_logs' => [
                'passive'     => false,
                'durable'     => true,
                'exclusive'   => false,
                'auto_delete' => false,
                'exchange'    => 'default',
                'routing.key' => 'students_logs',
                'channel'     => 'default',
            ],
        ]);

        $log = new Logger('students_logs');
        $log->pushHandler(new StudentLogsHandler($app['amqp.queues']['students_logs']));

        $syslog_ip = getenv("STUDENT_SYSLOG_IP");
        if (false === $syslog_ip) {
            throw new \Exception("The environment variable STUDENT_SYSLOG_IP is not set");
        }

        $syslog = new SyslogUdpHandler($syslog_ip);
        $syslog->setFormatter(new LineFormatter('%context%'));
        $log->pushHandler($syslog);

        $app['students_logs'] = $log;
    }
}
