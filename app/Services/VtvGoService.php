<?php

namespace App\Services;

use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseError;
use App\Http\Response\ResponseSuccess;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use MongoDB\BSON\ObjectId;

class VtvGoService
{
    public function __construct()
    {
    }

    /**
     * @return \App\Http\Response\ApiResponse
     */
    public function listChannel(): ApiResponse
    {
        $channels = [
            [
                'path' => 'truyen-hinh-truc-tuyen/vtv1-hd/',
                'thumbnail' => 'https://liftlearning.com/wp-content/uploads/2020/09/default-image.png',
                'title' => 'VTV 1',
                'chanel_name' => 'VTV 1'
            ],
            [
                'path' => 'truyen-hinh-truc-tuyen/vtv2-hd/',
                'thumbnail' => 'https://liftlearning.com/wp-content/uploads/2020/09/default-image.png',
                'title' => 'VTV 2',
                'chanel_name' => 'VTV 2'
            ],
            [
                'path' => 'truyen-hinh-truc-tuyen/vtv3-hd/',
                'thumbnail' => 'https://liftlearning.com/wp-content/uploads/2020/09/default-image.png',
                'title' => 'VTV 3',
                'chanel_name' => 'VTV 3'
            ]
        ];

        $output = [];
        foreach ($channels as $channel) {
            $output[] = [
                'video_oid' => (new ObjectId())->__toString(),
                'last_oid' => (new ObjectId())->__toString(),
                'video_id' => Arr::get($channel, 'path'),
                'thumbnail' => (string)Arr::get($channel, 'thumbnail'),
                'title' => 'Xem truyền hình trực tuyến '.(string)Arr::get($channel, 'title'),
                'time_text' => '--:--',
                'view_count_text' => 0,
                'chanel_name' => Arr::get($channel, 'chanel_name'),
                'chanel_url' => '',
                'published_time' => 'NOW'
            ];
        }

        return new ResponseSuccess([
            'list' => $output
        ]);
    }

    /**
     * @param string $url
     *
     * @return \App\Http\Response\ResponseError|\App\Http\Response\ResponseSuccess
     */
    public function linkPlay(string $url): ApiResponse
    {
        $urlNode = env('URL_NODE', 'http://youpip.net:3003');
        $response = Http::get("$urlNode/vtv?url=${url}")->json();
        preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\//', $response['data'], $matches);
        if (count($matches) != 2) {
            return new ResponseError();
        }
        $data = json_decode($matches[1], true);

        return new ResponseSuccess([
            'url' => Arr::get($data,'props.initialState.LiveTV.detailChannel.linkPlayHls')
        ]);
    }
}
