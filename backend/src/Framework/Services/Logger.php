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

    protected function getDirectoryPath(): string
    {
        return './../storage/logs';
    }

    protected function getFullPath(): string
    {
        return $this->getDirectoryPath().'/'.date('Y-m-d').".log";
    }

    protected function log(string $type, string $error, array $context = []): void
    {
        $directory = $this->getDirectoryPath();
        $fullPath = $this->getFullPath();

        if (!file_exists($directory)) {
          mkdir($directory, 0777, true);
        }

        file_put_contents(
            $fullPath,
            date('d.m.Y H:i:s').": {$type}: $error\n" . ($context ? print_r($context, true) : '') . PHP_EOL,
            FILE_APPEND
        );
    }

    public function error(string $error, array $context = [])
    {
        $this->log('ERROR', $error, $context);
    }

    public function info(string $info, array $context = [])
    {
        $this->log('INFO', $info, $context);
    }

}
