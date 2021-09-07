<?php

namespace Framework\Services;


use Framework\Services\Interfaces\Service;

/**
 * Class Session
 * @package Framework\Services
 * @method static Logger getInstance
 */
class Logger extends Service
{
    /**
     * @var self
     */
    protected static $instance;

    public function error(string $error)
    {
        $directory = $_SERVER['DOCUMENT_ROOT'].'/resources';
        $fullPath = $directory.'/'.date('Y-m-d').".log";

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents(
            $fullPath,
            date('H:i:s').": ERROR: $error\n",
            FILE_APPEND
        );
    }

    public function info(string $info)
    {
        $directory = $_SERVER['DOCUMENT_ROOT'].'/resources';
        $fullPath = $directory.'/'.date('Y-m-d').".log";

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents(
            $fullPath,
            date('H:i:s').": INFO: $info\n",
            FILE_APPEND
        );
    }

}
