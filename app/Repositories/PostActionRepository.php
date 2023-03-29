<?php

namespace App\Repositories;

use App\Enums\EActionPost;
use App\Models\PostAction;
use Illuminate\Database\Eloquent\Collection;
use Jenssegers\Mongodb\Eloquent\Model;

class PostActionRepository extends BaseRepository
{
    public function __construct(PostAction $model)
    {
        parent::__construct($model);
    }

    /**
     * @param int         $postId
     * @param string|null $lastOid
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function comment(int $postId, ?string $lastOid): Collection
    {
        $pipeline = [
            [
                '$match' => [
                    'post_id' => $postId,
                    'type' => EActionPost::TYPE_ACTION_COMMENT->value
                ]
            ],
            [
                '$lookup' => [
                    'from' => 'users',
                    'localField' => 'user_id',
                    'foreignField' => 'id',
                    'as' => 'users'
                ]
            ],
            [
                '$sort' => [
                    '_id' => -1
                ]
            ],
            [
                '$limit' => 50
            ],
            [
                '$project' => [
                    'user_id' => 1,
                    'content' => 1,
                    'full_name' => [
                        '$arrayElemAt'=> ['$users.full_name', 0]
                    ],
                    'action_oid' => [
                        '$toString' => '$_id'
                    ]
                ]
            ]
        ];

        return $this->model::raw(function ($collection) use ($pipeline) {
            /** @var \Jenssegers\Mongodb\Collection $collection */
            return $collection->aggregate($pipeline, [
                'typeMap' => self::OPTION_RESPONSE
            ]);
        });
    }
}
