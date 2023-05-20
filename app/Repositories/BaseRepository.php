<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\Operation\FindOneAndUpdate;

class BaseRepository
{
    const OPTION_RESPONSE = ['array' => 'array', 'document' => 'array', 'root' => 'array'];

    public function __construct(protected readonly Model $model)
    {
    }



    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->model->getTable();
    }

    /**
     * @param array $cond
     * @param array $column
     * @param array $sort
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function find(array $cond, array $column = ['*'], array $sort = ['_id' => 'asc']): Collection
    {
        $builder = $this->model::query();
        $builder->orderBy(array_key_first($sort), array_values($sort)[0]);

        return $builder->where($cond)->select($column)->get();
    }

    /**
     * @param array $cond
     * @param array $column
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function first(array $cond, array $column = ['*']): ?\Illuminate\Database\Eloquent\Model
    {
        return $this->model::query()->where($cond)->select($column)->first();
    }

    /**
     * @param array $cond
     * @param array $column
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function last(array $cond, array $column = ['*']): ?\Illuminate\Database\Eloquent\Model
    {
        return $this->model::query()->where($cond)->select($column)->orderByDesc('_id')->first();
    }

    /**
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data): \Illuminate\Database\Eloquent\Model
    {
        return $this->model::query()->create($data);
    }

    /**
     * @param array $cond
     *
     * @return int
     */
    public function deleteWhere(array $cond): int
    {
        return (int)$this->model::query()->where($cond)->delete();
    }

    /**
     * @param array $cond
     *
     * @return int
     */
    public function count(array $cond): int
    {
        return $this->model::query()->where($cond)->count();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        /** @var array $inc */
        $inc = DB::table('id_counters')->raw(function ($collection) {
            return $collection->findOneAndUpdate([
                'table_name' => $this->getTable(),
            ], [
                '$inc' => [
                    'id' => 1
                ]
            ], [
                'upsert' => true,
                'new' => true,
                'returnNewDocument' => true,
                'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER,
                'typeMap' => [
                    'array' => 'array',
                    'document' => 'array',
                    'root' => 'array'
                ]
            ]);
        });

        return Arr::get($inc, 'id', time());
    }

    /**
     * @param array $cond
     * @param array $update
     *
     * @return Model
     */
    public function findAndModify(array $cond, array $update): Model
    {
       return $this->model->raw(function ($collection) use ($cond,$update){
            return $collection->findOneAndUpdate($cond, $update, [
                'upsert' => true,
                'new' => true,
                'returnNewDocument' => true,
                'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER,
                'typeMap' => self::OPTION_RESPONSE
            ]);
        });
    }

    /**
     * @param array $cond
     * @param array $data
     *
     * @return int
     */
    public function update(array $cond,array $data): int
    {
        return $this->model::query()->where($cond)->update($data);
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function insert(array $data): bool
    {
        return $this->model::query()->insert($data);
    }
}
