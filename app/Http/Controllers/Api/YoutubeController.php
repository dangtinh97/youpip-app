<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\YoutubeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YoutubeController extends Controller
{
    public function __construct(protected YoutubeService $youtubeService)
    {
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function videoNew(Request $request): JsonResponse
    {
        $token = (string)$request->get('token', '');
        $result = $this->youtubeService->listVideo($token);

        return response()->json($result->toArray());
    }
}
