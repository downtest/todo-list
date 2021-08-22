<?php


namespace App\Http\BusinessServices;

use Framework\Services\DBMongo;
use Framework\Services\Interfaces\Service;
use MongoDB\BSON\ObjectId;

/**
 * Класс, хранящий бизнес-логику тасок
 * @package App\Http\BusinessServices
 */
class TasksInMongo extends Service
{
    protected DBMongo $db;

    public function __construct()
    {
        $this->db = DBMongo::getInstance();
    }

    /**
     * @param string $collectionName
     * @param string|null $oldParentId
     * @param string|null $newParentId
     * @param int $oldIndex
     * @param int $newIndex
     */
    public function updateParent(string $collectionName, ?string $oldParentId, ?string $newParentId, int $oldIndex, int $newIndex)
    {
        if ($oldParentId !== $newParentId) {
            // Родитель был изменён
            $this->removeFromParent($collectionName, $oldParentId, $oldIndex);
            $this->addToParent($collectionName, $newParentId, $newIndex);
        } else {
            // Изменяем индекс внутри одного родителя
            if ($oldIndex > $newIndex) {
                $this->db->updateMany(
                    $collectionName,
                    [
                        'parentId' => ['$eq' => $newParentId],
                        'index' => [
                            '$gte' => $newIndex,
                            '$lt' => $oldIndex
                        ],
                    ],
                    // Увеличиваем поле index на 1
                    ['$inc' => ['index' => 1]]
                );
            } else {
                $this->db->updateMany(
                    $collectionName,
                    [
                        'parentId' => ['$eq' => $newParentId],
                        'index' => [
                            '$gt' => $oldIndex,
                            '$lte' => $newIndex
                        ],
                    ],
                    // Увеличиваем поле index на 1
                    ['$inc' => ['index' => -1]]
                );
            }
        }
    }

    /**
     * Понижаем индекс у тасок старого родителя
     *
     * @param $collectionName
     * @param $parentId
     * @param $oldIndex
     */
    public function removeFromParent($collectionName, $parentId, $oldIndex)
    {
        $this->db->updateMany(
            $collectionName,
            [
                'parentId' => ['$eq' => $parentId],
                'index' => ['$gt' => $oldIndex],
            ],
            // Уменьшаем поле index на 1
            ['$inc' => ['index' => -1]]
        );
    }

    /**
     * Повышаем индекс у тасок нового родителя
     *
     * @param $collectionName
     * @param $parentId
     * @param $index
     */
    public function addToParent($collectionName, $parentId, $index)
    {
        $this->db->updateMany(
            $collectionName,
            [
                'parentId' => ['$eq' => $parentId],
                'index' => ['$gte' => $index],
            ],
            // Увеличиваем поле index на 1
            ['$inc' => ['index' => 1]]
        );
    }

    /**
     * @param array $idsToDelete Массив id`шников, которые будут удалены
     */
    public function deleteByIds(string $collection, array $idsToDelete): int
    {
        $idsToDelete = array_map(function($taskId) {
            if ($taskId instanceof ObjectId) {
                return $taskId;
            }

            return new ObjectId($taskId);
        }, $idsToDelete);

        return $this->db->deleteMany($collection, [
            '_id' => ['$in' => $idsToDelete],
        ]);
    }

    /**
     * Рекурсивно собирает всех потомков таски
     * @param string $collectionName
     * @param string $taskId
     * @param array|null $tasks
     * @return array
     */
    public function collectChildren(string $collectionName, string $taskId, ?array $tasks = null): array
    {
        if (!$tasks) {
            $allTasks = $this->db->find($collectionName, [], [
                //  Возвращаем только index
                'projection' => ['parentId' => 1],
            ]);
            $tasks = [];

            foreach ($allTasks as $task) {
                $tasks[$task['parentId'] ?? null][] = $task['id'];
            }

            unset($task);
        }

        $result = [$taskId];

        if (!empty($tasks[$taskId])) {
            foreach ($tasks[$taskId] as $task) {
                $result = array_merge($result, $this->collectChildren($collectionName, $task, $tasks));
            }

            unset($task);
        }

        return $result;
    }

    /**
     * @param string $collectionName
     * @param ?string $parentId
     * @return int
     */
    public function getMaxId(string $collectionName, ?string $parentId): int
    {
        return $this->db->find(
                $collectionName,
                ['parentId' => ['$eq' => $parentId]],
                [
                    //  Возвращаем только index
                    'projection' => ['index' => 1],
                    'sort' =>  ['index' => -1],
                    'limit' => 1,
                ]
            )[0]['index'] ?? 0;
    }

}
