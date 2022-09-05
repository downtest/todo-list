<?php


namespace App\Models;


class Collection extends Model
{
    /**
     * @var string|null
     */
    public static ?string $table = 'collections';

    public static function computeCollectionName(int $userId, string $collectionId): string
    {
        return "tasks{$userId}_{$collectionId}";
    }

}
