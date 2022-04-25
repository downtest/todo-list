<?php

namespace Framework\Http\Responses;

class JsonResponse extends \Laminas\Diactoros\Response\JsonResponse
{
    public function getDecodedData(): array
    {
        return json_decode($this->getBody(), true);
    }
}
