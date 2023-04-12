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
}
