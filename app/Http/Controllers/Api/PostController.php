<?php

namespace App\Http\Controllers\Api;

use App\Helper\StrHelper;
use App\Http\Controllers\Controller;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(protected readonly PostService $postService)
    {
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $lastPostOid = (string)$request->get('post_oid');
        if (!StrHelper::isObjectId($lastPostOid)) {
            $lastPostOid = '';
        }
        $data = $this->postService->crawlData();

        return response()->json($data->toArray());
    }
}