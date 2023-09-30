<?php

namespace App\Http\Controllers;

use App\Http\Requests\CallRequest;
use App\Services\CallService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function __construct(public CallService $callService)
    {
    }

    /**
     * @param CallRequest $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(CallRequest $request)
    {
        $call = $this->callService->index($request->get('fbid'));

        return view('call.index', compact('call'));
    }

    /**
     * @param CallRequest $request
     * @return JsonResponse
     */
    public function sendNotification(CallRequest $request): JsonResponse
    {
        $send = $this->callService->sendNotification($request->get('fbid'), $request->get('room_id'));

        return response()->json($send->toArray());
    }

    /**
     * @param CallRequest $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function answer(CallRequest $request)
    {
        $answer = $this->callService->answer($request->get('fbid'),$request->get('room-id'));

        return view('call.answer', compact('answer'));
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function destroyCall(string $id):JsonResponse
    {
        $remove = $this->callService->destroyCall($id);
        return response()->json($remove->toArray());
    }
}
