<?php

namespace App\Services;

use App\Repositories\TeamLunchMoneyRepository;
use Illuminate\Support\Arr;
use MongoDB\BSON\UTCDateTime;

class EatLunchService
{
    public function __construct(protected readonly TeamLunchMoneyRepository $teamLunchMoneyRepository)
    {
    }

    public function store(array $data): bool
    {
        $total = (int)$data['total'];

        $users = Arr::get($data, 'user');
        $inserts = [];
        foreach ($users as $userName) {
            $inserts[] = [
                'username' => $userName,
                'total' => $total,
                'paid' => false,
                'date' => date('Y/m/d'),
                'created_at' => new UTCDateTime(time() * 1000),
                'updated_at' => new UTCDateTime(time() * 1000)
            ];
        }
        $this->teamLunchMoneyRepository->insert($inserts);

        return true;
    }
}
