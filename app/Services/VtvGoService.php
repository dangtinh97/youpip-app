<?php

namespace App\Services;

use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseError;
use App\Http\Response\ResponseSuccess;
use App\Models\Log;
use App\Repositories\ConfigRepository;
use App\Repositories\LogRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class VtvGoService
{
    public function __construct(
        protected readonly LogRepository $logRepository,
        protected readonly ConfigRepository $configRepository
    ) {
    }

    /**
     * @return \App\Http\Response\ApiResponse
     */
    public function listChannel(): ApiResponse
    {
        $channels = [
            [
                'path' => 'truyen-hinh-truc-tuyen/vtv1-hd/',
                'thumbnail' => 'https://upload.wikimedia.org/wikipedia/commons/f/fc/Logo_VTV1_HD.png',
                'title' => 'VTV 1',
                'chanel_name' => 'VTV 1'
            ],
            [
                'path' => 'truyen-hinh-truc-tuyen/vtv2-hd/',
                'thumbnail' => 'https://upload.wikimedia.org/wikipedia/commons/9/91/VTV2%281%29.png',
                'title' => 'VTV 2',
                'chanel_name' => 'VTV 2'
            ],
            [
                'path' => 'truyen-hinh-truc-tuyen/vtv3-hd/',
                'thumbnail' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/48/VTV3_logo_2013_final.svg/320px-VTV3_logo_2013_final.svg.png',
                'title' => 'VTV 3',
                'chanel_name' => 'VTV 3'
            ],
            [
                'path' => 'truyen-hinh-truc-tuyen/vtv4-hd/',
                'thumbnail' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/48/VTV3_logo_2013_final.svg/320px-VTV3_logo_2013_final.svg.png',
                'title' => 'VTV 4',
                'chanel_name' => 'VTV 4'
            ],
            [
                'path' => 'truyen-hinh-truc-tuyen/vtv5-hd/',
                'thumbnail' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/48/VTV3_logo_2013_final.svg/320px-VTV3_logo_2013_final.svg.png',
                'title' => 'VTV 5',
                'chanel_name' => 'VTV 5'
            ],
            [
                'path' => 'truyen-hinh-truc-tuyen/vtv9-hd/',
                'thumbnail' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/48/VTV3_logo_2013_final.svg/320px-VTV3_logo_2013_final.svg.png',
                'title' => 'VTV 9',
                'chanel_name' => 'VTV 9'
            ],
            [
                'path' => 'truyen-hinh-truc-tuyen/thvl1-hd/',
                'thumbnail' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/48/VTV3_logo_2013_final.svg/320px-VTV3_logo_2013_final.svg.png',
                'title' => 'THVL 1 HD',
                'chanel_name' => 'THVL 1 HD'
            ],
            [
                'path' => 'truyen-hinh-truc-tuyen/vtc1-hd/',
                'thumbnail' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/48/VTV3_logo_2013_final.svg/320px-VTV3_logo_2013_final.svg.png',
                'title' => 'VTC 1',
                'chanel_name' => 'VTC 1'
            ],
            [
                'path' => 'truyen-hinh-truc-tuyen/vtc13-hd/',
                'thumbnail' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/48/VTV3_logo_2013_final.svg/320px-VTV3_logo_2013_final.svg.png',
                'title' => 'VTC 13',
                'chanel_name' => 'VTC 13'
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
                'view_count_text' => 'VieOn',
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
        $findConfig = $this->configRepository->first([
            'type' => 'PROXY'
        ]);
        $proxy = $findConfig['data'];
        /** @var \App\Models\Log|null $find */
        $find = $this->logRepository->last([
            'type' => 'VTV'.$url,
        ]);

        if ($find instanceof Log) {
            $urlPlay = $find->data ?? '';
            return new ResponseSuccess([
                'url' => $urlPlay
            ]);
        }

        if (!$find instanceof Log) {
            $urlNode = env('URL_NODE', 'http://youpip.net:3003');
            $response = Http::get("$urlNode/vtv?url={$url}&proxy={$proxy}")->json();
            preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\//', $response['data'], $matches);
            if (count($matches) != 2) {
                return new ResponseError();
            }
            $data = json_decode($matches[1], true);
            $urlPlay = Arr::get($data, 'props.initialState.LiveTV.detailChannel.linkPlayHls');
            $this->logRepository->create([
                'type' => 'VTV'.$url,
                'data' => $urlPlay
            ]);

            return new ResponseSuccess([
                'url' => $urlPlay
            ]);
        }

        return new ResponseError();
    }

    /**
     * @param string $url
     * @param string $json
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function updateVieOn(string $url, string $json): ApiResponse
    {
        try {
            $data = json_decode($json, true);
            $urlPlay = Arr::get($data, 'props.initialState.LiveTV.detailChannel.linkPlayHls');
            $modify = $this->logRepository->findAndModify([
                'type' => 'VTV'.$url,
            ], [
                '$set' => [
                    'data' => $urlPlay,
                    'updated_at' => new UTCDateTime(time() * 1000)
                ]
            ]);

            return new ResponseSuccess([
                'url_chanel'=> $url,
                'url_play' => $urlPlay
            ]);
        } catch (\Exception $exception) {
            return new ResponseError();
        }
    }
}
