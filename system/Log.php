<?php
namespace reading;

use reading\log\Writer;
use Monolog\Logger;

class Log 
{
    private static $_instance = [];

    /**
     * Create the logger.
     *
     * @return \Illuminate\Log\Writer
     */
    public static function getInstance($channel, $level = 'debug')
    {
        $uuid = $channel . '-' . $level;
        if (isset(self::$_instance[$uuid])) {
            return self::$_instance[$uuid];
        } else {
            $log = new Writer(
                new Logger($channel)
            );

            $level = Config::get('level', 'log') ? Config::get('level', 'log') : $level;
            $log->useDailyFiles(
                Config::get('path', 'log') . '/' . $channel . '-' . $level . '.log',
                0,
                $level
            );

            self::$_instance[$uuid] = $log;

            return $log;
        }
    }

    /**
     * record
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-11-30
     *
     * @param mixed $msg
     * @param string $channel
     * @param string $level
     * @param mixed $content
     * @return void
     */
    public static function record($msg, $channel = 'log', $level = 'debug', $content = []) 
    {   
        $uuid = $channel . '-' . $level;
        if (isset(self::$_instance[$uuid])) {
            $log = self::$_instance[$uuid];
        } else {
            $log = self::getInstance($channel, $level); 
        }

        $log->log($level, $msg, $content);
    }
}
