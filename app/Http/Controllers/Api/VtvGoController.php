<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VtvGoService;
use Illuminate\Http\Request;

class VtvGoController extends Controller
{
    public function __construct(protected readonly VtvGoService $vtvGoService)
    {
    }

    public function linkPlay(Request $request)
    {
        $url = "https://vtvgo.vn/".$request->get('path');
        $this->vtvGoService->linkPlay($url);
    }
}
