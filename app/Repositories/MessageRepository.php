<?php

namespace App\Repositories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;

class MessageRepository extends BaseRepository
{
    public function __construct(Message $model)
    {
        parent::__construct($model);
    }

    /**
     * @param int         $roomId
     * @param string|null $lastOid
     *
     * @return Collection
     */
    public function message(int $roomId, ?string $lastOid):Collection
    {
        $cond = [
            'room_id' => $roomId
        ];
        if ($lastOid) {
            $cond['_id'] = [
                '$lt' => new ObjectId($lastOid)
            ];
        }

        return $this->model::query()
            ->where($cond)
            ->orderByDesc('_id')
            ->limit(50)
            ->get();
    }
}