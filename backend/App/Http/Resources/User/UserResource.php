<?php


namespace App\Http\Resources\User;


use Framework\Http\Resources\Resource;
use Framework\Tools\Arr;

class UserResource extends Resource
{
    public function toArray(): ?array
    {
        return Arr::except($this->resource, [
            'password',
            'password_change_hash',
            'password_change_requested_at',
        ]) ?: null;
    }
}
