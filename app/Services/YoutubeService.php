<?php

namespace App\Services;

use App\Enums\ELinkYoutube;
use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseError;
use App\Http\Response\ResponseSuccess;
use App\Models\User;
use App\Repositories\LogRepository;
use App\Repositories\VideoRepository;
use App\Repositories\ViewRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use MongoDB\BSON\UTCDateTime;
use YouTube\Exception\YouTubeException;
use YouTube\YouTubeDownloader;

class YoutubeService
{
    /**
     * @param \App\Repositories\VideoRepository $videoRepository
     * @param \App\Repositories\ViewRepository  $viewRepository
     * @param \App\Repositories\LogRepository   $logRepository
     */
    public function __construct(
        protected readonly VideoRepository $videoRepository,
        protected readonly ViewRepository $viewRepository,
        protected readonly LogRepository $logRepository
    ) {
    }

    /**
     * @param string $token
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function listVideo(string $token): ApiResponse
    {
        $url = ELinkYoutube::BASE_URL->value.'/index?gl=VN';

        $body = Http::withHeaders($this->headerCountryCode())->get($url)->body();
        preg_match('/var ytInitialData =(.*?);</i', $body, $matches);
        if (count($matches) !== 2) {
            return new ResponseSuccess();
        }
        $varScript = trim($matches[1]);
        $data = json_decode($varScript, true);
        if (!is_array($data)) {
            return new ResponseSuccess();
        }
        $output = [];
        $contents = (array)Arr::get($data,
            'contents.twoColumnBrowseResultsRenderer.tabs.0.tabRenderer.content.richGridRenderer.contents', []);
        foreach ($contents as $content) {
            /** @var array $content */
            $videoRender = (array)Arr::get($content, 'richItemRenderer.content.videoRenderer');
            if (!$videoId = Arr::get($videoRender, 'videoId')) {
                continue;
            }
            $publishTime = (string)Arr::get($videoRender, 'publishedTimeText.simpleText');
            $timeText = (string)Arr::get($videoRender, 'lengthText.simpleText');
            if (!$publishTime || !$timeText) {
                continue;
            }
            $output[] = [
                'video_id' => $videoId,
                'thumbnail' => (string)Arr::get($videoRender, 'thumbnail.thumbnails.0.url'),
                'title' => (string)Arr::get($videoRender, 'title.runs.0.text'),
                'time_text' => $timeText,
                'view_count_text' => (string)Arr::get($videoRender, 'viewCountText.simpleText'),
                'chanel_name' => (string)Arr::get($videoRender, 'longBylineText.runs.0.text'),
                'chanel_url' => (string)Arr::get($videoRender,
                    'longBylineText.runs.0.navigationEndpoint.browseEndpoint.canonicalBaseUrl'),
                'published_time' => $publishTime
            ];
        }

        $this->saveVideo($output);

        return new ResponseSuccess([
            'list' => $output,
            'token' => $token
        ]);
    }

    /**
     * @param string $videoId
     *
     * @return \App\Http\Response\ApiResponse
     * @throws \Exception
     */
    function linkVideo(string $videoId): ApiResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $youtube = new YouTubeDownloader();
        $url = ELinkYoutube::BASE_URL->value."/watch?v={$videoId}";
        $output = [];
        try {
            $downloadOptions = $youtube->getDownloadLinks($url);
            /** @var array $combine */
            if (!$combine = $downloadOptions->getCombinedFormats()) {
                throw new \Exception("Not find getCombinedFormats link");
            }
            /** @var \YouTube\Models\StreamFormat $last */
            $last = Arr::last($combine);

            $url = $last->url;
            preg_match("/expire=(.*?)&/", $url, $matches);
            $timeExpire = (int)$matches[1];

            $output = [
                'mime_type' => $last->mimeType,
                'url' => $last->url,
                'quality' => $last->quality
            ];

            $this->viewRepository->findAndModify([
                'video_id' => $videoId,
                'user_id' => $user->id
            ], [
                '$inc' => [
                    'count' => 1
                ],
                '$set' => [
                    'updated_at' => new UTCDateTime()
                ]
            ]);

            $this->videoRepository->update([
                'video_id' => $videoId,
            ], [
                'video_play' => array_merge($output, [
                    'time_expire' => new UTCDateTime($timeExpire * 1000)
                ])
            ]);
        } catch (YouTubeException $e) {
            $this->logRepository->create([
                'type' => 'DEBUG',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'data' => [
                    'video_id' => $videoId
                ]
            ]);

            return new ResponseError();
        }

        return !$output ? new ResponseError() : new ResponseSuccess($output);
    }

    /**
     * @param array $data
     *
     * @return void
     */
    private function saveVideo(array $videos): void
    {
        foreach ($videos as $video) {
            $this->videoRepository->findAndModify([
                'video_id' => $video['video_id'] ?? ''
            ], [
                '$set' => [
                    'thumbnail' => $video['thumbnail'] ?? '',
                    'title' => $video['title'] ?? '',
                    'time_text' => $video['time_text'] ?? '',
                    'view_count_text' => $video['view_count_text'] ?? '',
                    'chanel_name' => $video['chanel_name'] ?? '',
                    'chanel_url' => $video['chanel_url'] ?? '',
                    'published_time' => $video['published_time'] ?? ''
                ]
            ]);
        }
    }

    /**
     * @param string $q
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function suggest(string $q): ApiResponse
    {
        if (!trim($q)) {
            return new ResponseError();
        }
        $time = (string)(time() * 1000);
        $url = "https://suggestqueries.google.com/complete/search?json=suggestCallBack&q={$q}&hl=vi&ds=yt&client=youtube&_={$time}";
        $body = Http::withHeaders($this->headerCountryCode())->get($url)->json();

        if (!is_array($body) || !$data = Arr::get($body, 1)) {
            return new ResponseError();
        }

        return new ResponseSuccess([
            'list' => $data
        ]);
    }

    /**
     * @param string $q
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function search(string $q): ApiResponse
    {
        if (!$q) {
            return $this->listVideo("");
        }
        $q = str_replace(" ", '+', $q);
        $url = ELinkYoutube::BASE_URL->value."/results?search_query={$q}&gl=VN";

        $body = Http::withHeaders($this->headerCountryCode())->get($url)->body();
        preg_match('/var ytInitialData =(.*?);</i', $body, $matches);
        if (count($matches) !== 2) {
            return new ResponseError();
        }

        $json = trim($matches[1]);
        $data = json_decode($json, true);
        $contents = (array)Arr::get($data,
            'contents.twoColumnSearchResultsRenderer.primaryContents.sectionListRenderer.contents.0.itemSectionRenderer.contents',
            []);
        $output = [];
        foreach ($contents as $content) {
            /** @var array $content */
            $videoRender = (array)Arr::get($content, 'videoRenderer');
            if (!is_array($videoRender) || !$videoId = Arr::get($videoRender, 'videoId')) {
                continue;
            }
            $publishTime = (string)Arr::get($videoRender, 'publishedTimeText.simpleText');
            $timeText = (string)Arr::get($videoRender, 'lengthText.simpleText');
            if (!$publishTime || !$timeText) {
                continue;
            }
            $output[] = [
                'video_id' => $videoId,
                'thumbnail' => (string)Arr::get($videoRender, 'thumbnail.thumbnails.0.url'),
                'title' => (string)Arr::get($videoRender, 'title.runs.0.text'),
                'time_text' => $timeText,
                'view_count_text' => (string)Arr::get($videoRender, 'viewCountText.simpleText'),
                'chanel_name' => (string)Arr::get($videoRender, 'longBylineText.runs.0.text'),
                'chanel_url' => (string)Arr::get($videoRender,
                    'longBylineText.runs.0.navigationEndpoint.browseEndpoint.canonicalBaseUrl'),
                'published_time' => $publishTime
            ];
        }
        $this->saveVideo($output);
        return new ResponseSuccess([
            'list' => $output,
            'q' => $q
        ]);
    }

    /**
     * @param string $videoId
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function videoSuggestById(string $videoId): ApiResponse
    {
        try{
            $output = [];
            if (empty($videoId)) {
                return $this->listVideo("");
            }
            $url = ELinkYoutube::BASE_URL->value."/watch?v=${videoId}";
            $body = Http::withHeaders($this->headerCountryCode())->get($url)->body();
            preg_match('/var ytInitialData = (.*?);</i', $body, $matches);

            $data = json_decode($matches[1], true);
            $contents = Arr::get($data, 'contents.twoColumnWatchNextResults.secondaryResults.secondaryResults.results',
                []);
            $tokenContinue = '';
            foreach ($contents as $content) {
                /** @var array $content */

                if($last = Arr::get($content,'continuationItemRenderer')){
                    $tokenContinue = Arr::get($last,'continuationEndpoint.continuationCommand.token');
                    continue;
                }
                $videoRender = (array)Arr::get($content, 'compactVideoRenderer');
                if (!is_array($videoRender) || !$videoId = Arr::get($videoRender, 'videoId')) {
                    continue;
                }
                $publishTime = (string)Arr::get($videoRender, 'publishedTimeText.simpleText');
                $timeText = (string)Arr::get($videoRender, 'lengthText.simpleText');
                if (!$publishTime || !$timeText) {
                    continue;
                }
                $output[] = [
                    'video_id' => $videoId,
                    'thumbnail' => (string)Arr::get($videoRender, 'thumbnail.thumbnails.0.url'),
                    'title' => (string)Arr::get($videoRender, 'title.simpleText'),
                    'time_text' => $timeText,
                    'view_count_text' => (string)Arr::get($videoRender, 'shortViewCountText.simpleText'),
                    'chanel_name' => (string)Arr::get($videoRender, 'shortBylineText.runs.0.text'),
                    'chanel_url' => (string)Arr::get($videoRender,
                        'shortBylineText.runs.0.navigationEndpoint.browseEndpoint.canonicalBaseUrl'),
                    'published_time' => $publishTime
                ];
            }

//            $this->loadMore($body,$tokenContinue);

            if(!$output){
                return new ResponseError();
            }
            $this->saveVideo($output);

            return new ResponseSuccess([
                'list' => $output,
                'video_id' => $videoId
            ]);
        }catch (\Exception $exception){
            return new ResponseError();
        }
    }

    /**
     * @return string[]
     */
    private function headerCountryCode(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36',
            'accept-language' => 'vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5',
            'cookie' => 'VISITOR_INFO1_LIVE=rCrrao3ZU0c; __Secure-3PSID=PwjDrmrGRx28gXZVBcoFnHKDS195PSnAiM0UGPpGzGl6zLb3nI7uMNDUDCD56fvSZrY0kg.; __Secure-3PAPISID=e8-JHVzWxfQttYbe/AnE2mByKl_ylGgJVR; __Secure-3PSIDCC=AIKkIs1ZuXq96BlXDgg0vASValBh82XhpWATl3ei3iDyMHBwTK2ulrCf0QFGq64vOA5l88MXWg; DEVICE_INFO=ChxOekU0TnpVM01UUTROalF3TnpFNE5EazVPQT09EK/E/Z0GGK/E/Z0G; PREF=tz=Asia.Saigon&f5=30000&f6=400&f7=100; GPS=1; YSC=bqVHeUR5PAU'
        ];
    }

    private function loadMore(string $body,string $token): array
    {
        return [];
        try{
            preg_match('/"INNERTUBE_CONTEXT":(.*?),"INNERTUBE_CONTEXT_CLIENT_NAME"/i',$body,$m2);
            $body = [
                'content' => json_decode($m2[1],true),
                'continuation' => $token
            ];

            $post = Http::post('https://www.youtube.com/youtubei/v1/next?key=AIzaSyAO_FJ2SlqU8Q4STEHLGCilw_Y9_11qcW8&prettyPrint=false');

            dd($body);
        }catch (\Exception $exception){
            return [];
        }
        return [];

    }
}
