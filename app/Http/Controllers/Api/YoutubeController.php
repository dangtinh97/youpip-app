<?php

namespace App\Http\Controllers\Api;

use App\Helper\StrHelper;
use App\Http\Controllers\Controller;
use App\Services\VtvGoService;
use App\Services\YoutubeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YoutubeController extends Controller
{
    public function __construct(
        protected YoutubeService $youtubeService,
        protected readonly VtvGoService $vtvGoService
    )
    {
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function videoNew(Request $request): JsonResponse
    {
        $lastOid = $request->get('last_oid','');
        $lastOid = StrHelper::isObjectId($lastOid) ? $lastOid : null;


        $result = match ($request->get('type')){
            "recently_view" => $this->youtubeService->recentlyView($lastOid),
            "vtv_go" => $this->vtvGoService->listChannel(),
            default => $this->youtubeService->listVideo("")
        };

        return response()->json($result->toArray());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function linkVideo(Request $request): JsonResponse
    {
        $videoId = (string)$request->get('video-id');
        $find = $this->youtubeService->linkVideo($videoId);

        return response()->json($find->toArray());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggest(Request $request): JsonResponse
    {
        $keyword = (string)$request->get('keyword', '');
        $result = $this->youtubeService->suggest($keyword);

        return response()->json($result->toArray());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $search = $this->youtubeService->search((string)$request->get('q'));

        return response()->json($search->toArray());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function videoSuggest(Request $request): JsonResponse
    {
        $videoId = (string)$request->get('video-id');
        $find = $this->youtubeService->videoSuggestById($videoId);

        return response()->json($find->toArray());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailVideo(Request $request): JsonResponse
    {
        $url = $request->get('url');
        $detail = $this->youtubeService->detailVideo($url);

        return response()->json($detail->toArray());
    }
}
