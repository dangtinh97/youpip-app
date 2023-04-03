<?php

namespace App\Repositories;

use App\Models\View;
use Illuminate\Database\Eloquent\Collection;
use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;

class ViewRepository extends BaseRepository
{
    public function __construct(View $model)
    {
        parent::__construct($model);
    }

    /**
     * @param int         $userId
     * @param string|null $lastOid
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function recentlyView(int $userId, ?string $lastOid): Collection
    {
        $match = [
            'user_id' => $userId
        ];
        if ($lastOid) {
            $match['_id'] = [
                '$lt' => new ObjectId($lastOid)
            ];
        }
        $pipeline = [
            [
                '$match' => $match
            ],
            [
                '$lookup' => [
                    'from' => 'videos',
                    'localField' => 'video_id',
                    'foreignField' => 'video_id',
                    'as' => 'videos'
                ]
            ],
            [
                '$sort' => [
                    '_id' => -1
                ]
            ],
            [
                '$limit' => 20
            ],
            [
                '$project' => [
                    'video' => [
                        '$arrayElemAt' => ['$videos', 0]
                    ]
                ]
            ]

        ];

        return $this->model::raw(function ($collection) use ($pipeline){
            return $collection->aggregate($pipeline,[
                'typeMap' => self::OPTION_RESPONSE
            ]);
        });
    }
}
