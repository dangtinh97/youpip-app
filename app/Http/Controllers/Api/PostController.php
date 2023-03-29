<?php

namespace App\Http\Controllers\Api;

use App\Enums\EActionPost;
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
        $lastPostOid = (string)$request->get('post_last_oid');
        if (!StrHelper::isObjectId($lastPostOid)) {
            $lastPostOid = null;
        }
        $data = $this->postService->index($lastPostOid,null);

        return response()->json($data->toArray());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $content = (string)$request->get('content');
        $image = (int)$request->get('attachment_id');
        $create = $this->postService->create($content, $image);

        return response()->json($create->toArray());
    }

    /**
     * @param string                   $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reaction(string $id, Request $request): JsonResponse
    {
        $reaction = (string)$request->get('action', EActionPost::LIKE->value);
        $action = $this->postService->reaction($id, $reaction);

        return response()->json($action->toArray());
    }
}
