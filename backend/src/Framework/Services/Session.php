<?php

namespace Framework\Services;


use Exception;
use Framework\Services\Interfaces\Service;

/**
 * Class Session
 * @package Framework\Services
 * @method static Session getInstance
 */
class Session extends Service
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
    public static function fromGlobal (): self
    {
        $session = static::getInstance();

        $session->setMany($_SESSION);

        return $session;
    }

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function set (string $key, $value): self
    {
        $this->values[$key] = $value;
        $_SESSION[$key] = $value;

        return $this;
    }

    /**
     * @param array $sessionArr
     * @return $this
     */
    public function setMany (array $sessionArr): self
    {
        $this->values = $sessionArr;
        $_SESSION = $sessionArr;

        return $this;
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
