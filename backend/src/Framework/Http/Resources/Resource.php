<?php


namespace Framework\Http\Resources;


abstract class Resource
{
    /**
     * @var
     */
    protected $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public static function collection(iterable $resources): array
    {
        $result = [];

        foreach ($resources as $resource) {
            $result[] = (new static($resource))->toArray();
        }

        return $result;
    }

    abstract public function toArray(): ?array;
}
