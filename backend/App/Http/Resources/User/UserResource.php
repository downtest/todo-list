<?php


namespace App\Http\Resources\User;


use Framework\Http\Resources\Resource;
use Framework\Tools\Arr;

class UserResource extends Resource
{
    public function toArray(): array
    {
        return Arr::except($this->resource, ['password']);
    }
}
