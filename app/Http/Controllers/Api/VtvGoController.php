<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VtvGoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VtvGoController extends Controller
{
    public function __construct(protected readonly VtvGoService $vtvGoService)
    {
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function linkPlay(Request $request): JsonResponse
    {
        $url = "https://vieon.vn/".$request->get('path');
        $response = $this->vtvGoService->linkPlay($url);

        return response()->json($response->toArray());
    }
}
