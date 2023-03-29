<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;

class PostRepository extends BaseRepository
{
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    /**
     * @param string|null $lastOid
     * @param int|null    $userId
     *
     * @return Collection
     */
    public function index(?string $lastOid, ?int $userId): Collection
    {
        $match = [
            'deleted_at' => null,
        ];
        if ($userId) {
            $match['user_id'] = $userId;
        }
        if ($lastOid) {
            $match['_id'] = [
                '$lt' => new ObjectId($lastOid)
            ];
        }
        $pipeline = [
            ['$match' => $match],
            [
                '$lookup' => [
                    'from' => 'attachments',
                    'localField' => 'attachment_id',
                    'foreignField' => 'id',
                    'as' => 'image'
                ]
            ],
            [
                '$lookup' => [
                    'from' => 'users',
                    'localField' => 'user_id',
                    'foreignField' => 'id',
                    'as' => 'users'
                ],
            ],
            [
                '$sort' => [
                    'id' => -1
                ]
            ],
            [
                '$limit' => 20
            ]
        ];

        return $this->model::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline, ['typeMap' => self::OPTION_RESPONSE]);
        });
    }
}
