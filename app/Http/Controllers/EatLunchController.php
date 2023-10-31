<?php

namespace App\Http\Controllers;

use App\Services\EatLunchService;
use Illuminate\Http\Request;

class EatLunchController extends Controller
{
    public function __construct(protected readonly EatLunchService $eatLunchService)
    {
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
     */
    public function create(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $users = [
            'nhatnv' => 'A. Nhat',
            'tinhvd' => 'Tinh Vd',
            'namhn' => 'Nam Hn',
            'longdd' => 'Long Dinh',
            'dungdc' => 'Anh Dung',
            'luongnd' => 'Luong Android',
            'duong' => 'Duong',
            'quanm' => 'Quan',
            'thuynt' => 'C. Thuy',
            'tuantb' => 'A. Tuan'
        ];

        return view('eat_lunch.create', compact('users'));
    }

    public function store(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $this->eatLunchService->store($request->toArray());

        return view('eat_lunch.index');
    }
}
