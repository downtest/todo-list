<?php

namespace Framework\Services;


use App\Http\Exceptions\AppException;
use Framework\Services\Interfaces\Service;

/**
 * Class Session
 * @package Framework\Services
 * @method static Curl getInstance
 */
class Curl extends Service
{
    /**
     * @var static
     */
    protected static $instance;

    protected string $url;

    /**
     * @var string|bool Ответ от сервера
     */
    protected string|bool $htmlResponse;


    public function get(string $url, $headers = []): static
    {
        $this->request('GET', $url, [], $headers);

        return $this;
    }


    public function post(string $url, array $params = [], $headers = []): static
    {
        $this->request('POST', $url, $params, $headers);

        return $this;
    }

    protected function request(string $method, string $url, array $params = [], $headers = [])
    {
        $ch = curl_init($url);

        if (mb_strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER , $headers);
        }

        $html = curl_exec($ch);
        curl_close($ch);

        $this->htmlResponse = $html;
    }

    /**
     * @throws AppException
     */
    public function decodeJson(): array
    {
        $json = json_decode($this->htmlResponse, true);

        if (json_last_error()) {
            throw new AppException(json_last_error_msg(), 500, null, [$this->htmlResponse]);
        }

        return $json;
    }
}
