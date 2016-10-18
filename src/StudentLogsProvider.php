<?php

namespace ETNA\Silex\Provider\StudentLogs;

use Pimple\ServiceProviderInterface;
use Pimple\Container;

class StudentLogsProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        if (false === isset($app["rabbit.producers"]) && false === isset($app["rabbit.producers"]["students_logs"])) {
            throw new \Exception("The StudentLogsProvider requires the students_logs RabbitMQ queue to exists");
        }
        if (false === isset($app["logs"])) {
            throw new \Exception("The StudentLogsProvider requires the app logger");
        }

        $app["students_logs"] = function ($app) {
            return new StudentLogs($app);
        };
    }
}
