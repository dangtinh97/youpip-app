<?php

namespace App\Services;

use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseSuccess;
use App\Repositories\Work\BoardRepository;
use Illuminate\Support\Arr;
use MongoDB\BSON\ObjectId;

class WorkMemoService
{
    public function __construct(protected readonly BoardRepository $boardRepository)
    {
    }

    /**
     * @param string $title
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function storeBoard(string $title): ApiResponse
    {
        /** @var \App\Models\Board $create */
        $create = $this->boardRepository->create([
            'id' => $this->boardRepository->getId(),
            'title' => $title,
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

        $dataCreate = $create->toArray();
        $dataCreate['data'] = array_map(function ($item) {
            $item['_id'] = (string)$item['_id'];
            $item['items'] = [];
            return $item;
        }, $dataCreate['data']);

        return new ResponseSuccess($dataCreate);
    }

    /**
     * @return \App\Http\Response\ApiResponse
     */
    public function index():ApiResponse
    {
       $list =  $this->boardRepository->find([
            '_id'=> [
                '$exists' => true
            ]
        ],['*'],['_id' => 'desc']);

       if($list->isEmpty()){
           return new ResponseSuccess([
               'list' => []
           ]);
       }
       $idFirst = $list->first()->_id;
       $result = $list->map(function ($item){
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
}
