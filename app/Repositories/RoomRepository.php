<?php

namespace App\Repositories;

use App\Enums\EActionPost;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;
use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;

class RoomRepository extends BaseRepository
{
    public function __construct(Room $model)
    {
        parent::__construct($model);
    }

    /**
     * @param int         $userId
     * @param string|null $lastOid
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listChat(int $userId, ?string $lastOid): Collection
    {
        $match = [
            'join' => [
                '$in' => [$userId]
            ]
        ];
        $match['join'] = [
            '$exists' => true
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
                    'from' => 'users',
                    'let' => ['join' => '$join'],
                    'pipeline' => [
                        [
                            '$match' => [
                                '$expr' => [
                                    '$and' => [
                                        [
                                            '$in' => ['$id','$$join']
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'as' => 'users'
                ]
            ],
            [
                '$sort' => [
                    'updated_at' => -1
                ]
            ],
            [
                '$limit' => 20
            ]
        ];

        return $this->model::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline,[
                'typeMap' => self::OPTION_RESPONSE
            ]);
        });
    }
}
