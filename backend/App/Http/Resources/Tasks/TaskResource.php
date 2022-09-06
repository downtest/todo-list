<?php


namespace App\Http\Resources\Tasks;


use Framework\Http\Resources\Resource;

class TaskResource extends Resource
{
    public function toArray(): array
    {
        return [
            'id' => $this->resource['id'],
            'index' => $this->resource['index'] ?? null, // индекс сортировки
            'message' => $this->resource['message'] ?? '',
            'confirmed' => true, // Флаг, что загружено на сервер(что фронт совпадает с бэком)
            'parentId' => $this->resource['parentId'] ?? null, // родительский id
            'children' => $this->resource['children'] ?? [], // Массив дочерних id
            'labels' => $this->resource['labels'] ?? [],
            'date' => $this->resource['date'] ?? null,
            'date_utc' => $this->resource['date_utc'] ?? null,
            'time' => $this->resource['time'] ?? null,
            'time_utc' => $this->resource['time_utc'] ?? null,
            'informed' => $this->resource['informed'] ?? null,
        ];
    }
}
