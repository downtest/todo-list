<?php

namespace Framework\Services;


use Exception;
use Framework\Services\Interfaces\Service;

/**
 * Class Session
 * @package Framework\Services
 * @method static Headers getInstance
 */
class Headers extends Service
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     * @var array
     */
    protected array $values = [];

    /**
     * @return static
     * @throws Exception
     */
    public static function fromGlobal (): Headers
    {
        $service = static::getInstance();

        foreach($_SERVER as $key => $value) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $service->set($header, $value);
        }

        return $service;
    }

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function set (string $key, $value): self
    {
        $this->values[$key] = $value;

        return $this;
    }

    /**
     * @param array $sessionArr
     * @return $this
     */
    public function setMany (array $sessionArr): self
    {
        $this->values = $sessionArr;

        return $this;
    }

    /**
     * @return array
     */
    public function all (): array
    {
        return $this->values;
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function get (string $key, $default = null)
    {
        return $this->values[$key] ?? $default;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function unset (string $key): self
    {
        unset ($this->values[$key], $_SESSION[$key]);

        return $this;
    }
}
