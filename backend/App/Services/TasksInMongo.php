<?php


namespace App\Services;

use App\Http\Resources\Tasks\TaskResource;
use Framework\Services\DBMongo;
use Framework\Services\Interfaces\Service;
use Framework\Tools\Arr;
use MongoDB\BSON\ObjectId;

/**
 * Класс, хранящий бизнес-логику тасок
 * @package App\Http\BusinessServices
 */
class TasksInMongo extends Service
{
    protected static $instance;

    protected DBMongo $db;

    public function __construct()
    {
        $this->db = DBMongo::getInstance();
    }

    /**
     * @param string $collectionName
     * @param string $taskId ID таски, которую передвинули
     * @param string|null $oldParentId
     * @param string|null $newParentId
     * @param int $oldIndex
     * @param int $newIndex
     */
    public function updateParent(string $collectionName, string $taskId, ?string $oldParentId, ?string $newParentId, int $oldIndex, int $newIndex)
    {
        if ($oldParentId !== $newParentId) {
            // Родитель был изменён
            $this->removeFromParent($collectionName, $oldParentId, $oldIndex);
            $this->addToParent($collectionName, $newParentId, $newIndex);
        } else {
            // Изменяем индекс внутри одного родителя
            if ($oldIndex > $newIndex) {
                // Увеличили индекс(опустил ниже)
                $this->db->updateMany(
                    $collectionName,
                    [
                        'parentId' => ['$eq' => $newParentId],
                        'id' => ['$ne' => $taskId],
                        'index' => [
                            '$gte' => $newIndex,
                            '$lt' => $oldIndex
                        ],
                    ],
                    // Увеличиваем поле index на 1
                    ['$inc' => ['index' => 1]]
                );
            } else {
                // Уменьшили индекс(подняли выше)
                $this->db->updateMany(
                    $collectionName,
                    [
                        'parentId' => ['$eq' => $newParentId],
                        'id' => ['$ne' => $taskId],
                        'index' => [
                            '$gt' => $oldIndex,
                            '$lte' => $newIndex
                        ],
                    ],
                    // Уменьшаем поле index на 1
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
                //  Возвращаем только parentId
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
     * Рекурсивно добавляет, либо обновляет все записи(таски) и змассива
     * Корректно работает только при первом создании, когда добавляем и первый уровень(parentId = null), и, если нужно, последующие
     *
     * @param string $collectionName
     * @param array $nodes
     * @param string|null $oldParentId Старый(временный) родительский ID, по которому ищем запись в массиве
     * @param string|null $newParentId Новый родительский ID, который
     * @return array
     * @throws \Exception
     */
    public function massUpdateOrCreate(string $collectionName, array $nodes, ?string $oldParentId = null, ?string $newParentId = null): array
    {
        $children = array_filter($nodes, fn($task) => $task['parentId'] === $oldParentId);
        $result = [];
        $handledTaskIds = [];

        foreach ($children as $node) {
            // Заменяем временный ID на реальный
            if (!empty($node['parentId']) && $newParentId) {
                $node['parentId'] = $newParentId;
            }

            $actualizedTask = $this->createOrUpdate($collectionName, $node);

            $result[] = $actualizedTask;
            $handledTaskIds[] = $node['id'];

            // Рекурсивно добавляем всех дочек
            if (count(array_filter($nodes, fn($nodeInLoop) => !empty($nodeInLoop['parentId']) && $nodeInLoop['parentId'] === $node['id'])) > 0) {
                $result = array_merge($result, $this->massUpdateOrCreate($collectionName, $nodes, $actualizedTask['oldId'], $actualizedTask['id']));

                foreach ($result as $item) {
                    $handledTaskIds[] = $item['id'];
                    $handledTaskIds[] = $item['oldId'];
                }
            }
        }

        // Только на 1ом уровне рекурсии нам надо сохранить все ранее не сохранённые строки (Когда мы добавляем только дочерние элементы)
        if ($oldParentId === null) {
            $unhandledTasks = array_filter($nodes, fn($node) => !in_array($node['id'], $handledTaskIds, true));

            foreach ($unhandledTasks as $node) {
                $this->createOrUpdate($collectionName, $node);
            }
        }

        return $result;
    }

    public function createOrUpdate(string $collectionName, array $task): array
    {
        if (isset($task['isNew']) && $task['isNew']) {
            // Нужно создать
            $newId = $this->create($collectionName, $task);

            return [
                'oldId' => $task['id'],
                'id' => $newId,
            ] + (new TaskResource($task))->toArray();
        } else {
            // Нужно обновить
            $this->update($collectionName, $task);

            return [
                'oldId' => $task['id'],
            ] + (new TaskResource($task))->toArray();
        }
    }

    /**
     * Создаём новую запись
     *
     * @param string $collectionName
     * @param array $task
     * @return string
     */
    public function create(string $collectionName, array $task): string
    {
        $this->addToParent(
            $collectionName,
            $task['parentId'] ?? null,
            $task['index'] ?? 0
        );

//        $maxIndex = $this->getMaxId($collectionName, $request->getAttribute('parentId') ?? null);

        return $this->db->insertOne($collectionName, Arr::except($task, ['isNew', 'id', 'confirmed']));
    }

    /**
     * Изменение записи
     *
     * @param string $collectionName
     * @param array $task
     * @return bool
     * @throws \Exception
     */
    public function update(string $collectionName, array $taskChanges): bool
    {
        if (!$taskTmp = $this->db->findById($collectionName, $taskChanges['id'])) {
            throw new \Exception("Не найдена таска #{$taskChanges['id']}");
        }


        // Если изменён родитель, но не передан индекс в новом родителе
//        if ($taskChanges['parentId'] && empty($taskChanges['index'])) {
//            echo "\nsetting index max\n";
//            $maxIndex = $this->getMaxId($collectionName, $taskChanges['parentId']);
//
//            $taskChanges['index'] = $maxIndex;
//        }

        if (isset($taskChanges['parentId']) || isset($taskChanges['index'])) {
            TasksInMongo::getInstance()->updateParent(
                $collectionName,
                $taskChanges['id'],
                $taskTmp['parentId'] ?? null,
                $taskChanges['parentId'] ?? null,
                $taskTmp['index'] ?? 0,
                $taskChanges['index'] ?? 0
            );
        }

        return $this->db->updateOne($collectionName, Arr::except($taskChanges, ['collectionId', 'taskId', 'isNew', 'confirmed', 'updated']));
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
