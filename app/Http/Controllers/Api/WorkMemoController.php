<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\ResponseError;
use App\Services\WorkMemoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkMemoController extends Controller
{
    public function __construct(protected readonly WorkMemoService $workMemoService)
    {
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeBoard(Request $request): JsonResponse
    {
        $title = (string)$request->get('title');
        if(empty($title)){
            $result = new ResponseError(422,"Title is required!");
        }else{
            $result = $this->workMemoService->storeBoard($title);
        }

        return response()->json($result->toArray());
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $index = $this->workMemoService->index();

        return response()->json($index->toArray());
    }

    /**
     * @param int                      $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeListWork(int $id,Request $request): JsonResponse
    {
        $store = $this->workMemoService->storeListWork($id,(string)$request->get('title'));

        return response()->json($store->toArray());
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailBoard(int $id): JsonResponse
    {
        $detail = $this->workMemoService->detailBoard($id);

        return response()->json($detail->toArray());
    }
}
