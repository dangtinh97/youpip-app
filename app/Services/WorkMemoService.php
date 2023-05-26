<?php

namespace App\Services;

use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseError;
use App\Http\Response\ResponseSuccess;
use App\Models\Board;
use App\Repositories\Work\BoardRepository;
use App\Repositories\Work\WorkRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use MongoDB\BSON\ObjectId;

class WorkMemoService
{
    public function __construct(
        protected readonly BoardRepository $boardRepository,
        protected readonly WorkRepository $workRepository
    ) {
    }

    /**
     * @param string $title
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function storeBoard(string $title): ApiResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        /** @var \App\Models\Board $create */
        $create = $this->boardRepository->create([
            'id' => $this->boardRepository->getId(),
            'user_id' => $user->id,
            'title' => $title,
            'active' => true,
            'data' => [
                [
                    '_id' => new ObjectId(),
                    'title' => 'Đang làm',
                    'sort' => 1,
                ],
                [
                    '_id' => new ObjectId(),
                    'title' => 'Việc cần làm',
                    'sort' => 2,
                ],
                [
                    '_id' => new ObjectId(),
                    'title' => 'Hoàn thành',
                    'sort' => 3,
                ],
            ]
        ]);
        $this->setActive($create->id, $user->id);
        $dataCreate = $create->toArray();

        $dataCreate['data'] = array_map(function ($item) {
            $item['_id'] = (string)$item['_id'];
            $item['items'] = [];

            return $item;
        }, $dataCreate['data']);

        return new ResponseSuccess($dataCreate);
    }

    public function setActive(int $id, int $userId)
    {
        $this->boardRepository->update([
            'id' => [
                '$ne' => $id
            ],
            'user_id' => $userId
        ], [
            'active' => false
        ]);
        $this->boardRepository->update([
            'id' => $id,
            'user_id' => $userId
        ], [
            'active' => true
        ]);
    }

    /**
     * @return \App\Http\Response\ApiResponse
     */
    public function index(): ApiResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $list = $this->boardRepository->find([
            'user_id' => $user->id
        ], ['*'], ['_id' => 'desc']);

        if ($list->isEmpty()) {
            return new ResponseSuccess([
                'list' => []
            ]);
        }
        $idFirst = $list->first()->_id;
        $result = $list->map(function ($item) {
            /** @var \App\Models\Board $item */
            $data = $item->toArray();
            $data['data'] = array_map(function ($item) {
                $item['_id'] = (string)$item['_id'];
                $item['items'] = [];

                return $item;
            }, $data['data']);

            return $data;
        });

        return new ResponseSuccess([
            'list' => $result->toArray()
        ]);
    }

    public function getWorkById()
    {
    }

    /**
     * @param int    $boardId
     * @param string $title
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function storeListWork(int $boardId, string $title): ApiResponse
    {
        $objectId = new ObjectId();
        $this->boardRepository->findAndModify([
            'id' => $boardId
        ], [
            '$push' => [
                'data' => [
                    '_id' => $objectId,
                    'title' => $title,
                    'sort' => 1
                ]
            ]
        ]);

        return new ResponseSuccess([
            '_id' => (string)$objectId
        ]);
    }

    /**
     * @param int $id
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function detailBoard(int $id): ApiResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        /** @var \App\Models\Board|null $board */
        $board = $this->boardRepository->first([
            'user_id' => $user->id,
            'id' => $id
        ]);

        if (!$board instanceof Board) {
            return new ResponseError(204);
        }

        $works = $this->workRepository->find([
            'board_id' => $id
        ]);

        $this->setActive($id, $user->id);

        $data = $board->toArray();
        $data['data'] = array_map(function ($item) use ($works) {
            $item['_id'] = (string)$item['_id'];
            $item['works'] = array_values($works->where('job_list_id', new ObjectId((string)$item['_id']))->toArray());

            return $item;
        }, $data['data']);

        return new ResponseSuccess($data);
    }

    /**
     * @param string $itemListId
     * @param string $title
     *
     * @return ApiResponse
     */
    public function storeWork(string $itemListId, string $title): ApiResponse
    {
        /** @var \App\Models\Board $board */
        $board = $this->boardRepository->first([
            'data._id' => new ObjectId($itemListId)
        ]);
        $boardId = $board->id;
        /** @var \App\Models\Work $create */
        $create = $this->workRepository->create([
            'title' => $title,
            'board_id' => $boardId,
            'job_list_id' => new ObjectId($itemListId)
        ]);

        return new ResponseSuccess([
            '_id' => $create->_id
        ]);
    }
}
