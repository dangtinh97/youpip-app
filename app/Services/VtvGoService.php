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
                'path' => 'xem-truc-tuyen-kenh-vtv1-1.html',
                'thumbnail' => 'https://liftlearning.com/wp-content/uploads/2020/09/default-image.png',
                'title' => 'VTV 1',
                'chanel_name' => 'VTV 1'
            ],
            [
                'path' => 'xem-truc-tuyen-kenh-vtv2-2.html',
                'thumbnail' => 'https://liftlearning.com/wp-content/uploads/2020/09/default-image.png',
                'title' => 'VTV 2',
                'chanel_name' => 'VTV 2'
            ],
            [
                'path' => 'xem-truc-tuyen-kenh-vtv3-3.html',
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

    public function linkPlay(string $url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://vieon.vn/truyen-hinh-truc-tuyen/vtv1-hd/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\//',$response,$matches);
        if(count($matches)!=2){
            return new ResponseError();
        }
        dd($matches[1]);
        $data = json_decode($matches[1],true);
        dd($data);
        dd($matches);



        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36';
        $end = Arr::last(explode('-',$url));
        $body = Http::withHeaders([
            'user-agent' => $userAgent
        ])->get($url);
        $headers = $body->headers();


        preg_match('/var token = \'(.*?)\';/',$body->body(),$m);
        if(count($m)!==2){
            return new ResponseError();
        }
        $token = $m[1];
        $id =  (int)$end;
        $time = explode('.',$token)[0];
        /**
        type_id: 1
        id: 2
        time: 1681275630
        token: 1681275630.7571afc4bd6e72a1fabc6f115233570a
         */

        $json = Http::withHeaders([
            'user-agent' => $userAgent,
            'Cookie' => implode("; ",Arr::get($headers,'Set-Cookie'))
        ])->post('https://vtvgo.vn/ajax-get-stream',[
           'type_id' => 1,
           'id' => $id,
           'time' => $time,
           'token' => $token
        ])->json();
        dd($json);
    }
}
