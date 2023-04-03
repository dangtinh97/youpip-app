<?php

namespace App\Http\Controllers\Api;

use App\Enums\EActionPost;
use App\Enums\EStatusApi;
use App\Helper\StrHelper;
use App\Http\Controllers\Controller;
use App\Http\Response\ResponseError;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function postMe(Request $request): JsonResponse
    {
        $lastPostOid = (string)$request->get('post_last_oid');
        if (!StrHelper::isObjectId($lastPostOid)) {
            $lastPostOid = null;
        }
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $this->postService->index($lastPostOid, $user->id);

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

    /**
     * @param string                   $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function comment(string $id, Request $request): JsonResponse
    {
        $comment = $this->postService->comment((string)$request->get('content'), $id);

        return response()->json($comment->toArray());
    }

    /**
     * @param string                   $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listComment(string $id, Request $request): JsonResponse
    {
        $lastOid = (string)$request->get('last_comment_oid', '');
        if (!StrHelper::isObjectId($lastOid)) {
            $lastOid = null;
        }

        $comments = $this->postService->listComment($id, $lastOid);

        return response()->json($comments->toArray());
    }

    /**
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $id): JsonResponse
    {
        if (!StrHelper::isObjectId($id)) {
            return response()->json((new ResponseError(EStatusApi::FAIL->value))->toArray());
        }
        $delete = $this->postService->deletePost($id);

        return response()->json($delete->toArray());
    }
}
