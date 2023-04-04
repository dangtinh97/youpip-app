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
use KubAT\PhpSimple\HtmlDomParser;
use MongoDB\BSON\ObjectId;
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
        $url = ELinkYoutube::BASE_URL->value.'/index?gl=VN&hl=vi';

        $body = Http::withHeaders($this->headerCountryCode())
            ->get($url)->body();
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
                'video_oid' => (new ObjectId())->__toString(),
                'last_oid' => (new ObjectId())->__toString(),
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
     * @param string|null $lastOid
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function recentlyView(?string $lastOid): ApiResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $views = $this->viewRepository->recentlyView($user->id, $lastOid);
        $maps = $views->map(function ($item) {
            /** @var \App\Models\View $item */

            $videoRender = $item->getAttribute('video') ?? [];

            return [
                'last_oid'=> $item->_id,
                'video_oid' => (string)Arr::get($videoRender, '_id'),
                'video_id' => (string)Arr::get($videoRender, 'video_id'),
                'thumbnail' => (string)Arr::get($videoRender, 'thumbnail'),
                'title' => (string)Arr::get($videoRender, 'title'),
                'time_text' => (string)Arr::get($videoRender, 'time_text'),
                'view_count_text' => (string)Arr::get($videoRender, 'view_count_text'),
                'chanel_name' => (string)Arr::get($videoRender, 'chanel_name'),
                'chanel_url' => (string)Arr::get($videoRender, 'chanel_url'),
                'published_time' => (string)Arr::get($videoRender, 'published_time')
            ];
        });

        return new ResponseSuccess([
            'list' => $maps->toArray()
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
            $url = $this->crawlFromWebOther($videoId);
            if(!$url){
                $downloadOptions = $youtube->getDownloadLinks($url);
                if (!$combine = $downloadOptions->getCombinedFormats()) {
                    throw new \Exception("Not find getCombinedFormats link");
                }

                /** @var \YouTube\Models\StreamFormat $last */
                $last = Arr::last($combine);

                $url = $last->url;
            }

            preg_match("/expire=(.*?)&/", $url, $matches);
            $timeExpire = (int)$matches[1];

            $output = [
                'mime_type' => "",
                'url' => $url,
                'quality' => ""
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

            $update = [
                'video_play' => array_merge($output, [
                    'time_expire' => new UTCDateTime($timeExpire * 1000)
                ])
            ];

            $this->videoRepository->update([
                'video_id' => $videoId,
            ], $update);
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
     * @param $videoId
     *
     * @return string|null
     */
    private function crawlFromWebOther($videoId): ?string
    {
        try {
            $url = "https://truyenaudio247.com/GetlinkYoutube";
            $data = Http::post($url, [
                'TuKhoa' => "https://www.youtube.com/watch?v={$videoId}"
            ])->body();

            /** @var \simple_html_dom\simple_html_dom $dom */
            $dom = HtmlDomParser::str_get_html($data);

            /** @var \simple_html_dom\simple_html_dom_node $first */
            $first = $dom->find('a[class="btn btn-outline-danger btn-sm"]')[0];
            $url = (string)str_replace("&amp;", "&", $first->attr['href']);

            return substr($url, 0, strpos($url, "&title"));
        } catch (\Exception $exception) {
            return null;
        }
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
                $thumbnails = Arr::get($videoRender, 'thumbnail.thumbnails');
                $output[] = [
                    'video_id' => $videoId,
                    'thumbnail' => (string)(Arr::last($thumbnails)['url'] ?? ''),
                    'title' => (string)Arr::get($videoRender, 'title.simpleText'),
                    'time_text' => $timeText,
                    'view_count_text' => (string)Arr::get($videoRender, 'shortViewCountText.simpleText'),
                    'chanel_name' => (string)Arr::get($videoRender, 'shortBylineText.runs.0.text'),
                    'chanel_url' => (string)Arr::get($videoRender,
                        'shortBylineText.runs.0.navigationEndpoint.browseEndpoint.canonicalBaseUrl'),
                    'published_time' => $publishTime
                ];
            }

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
//            'accept-language' => 'vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5',
            'accept-language' => 'vi-VN,vi;q=0.9',
            'cookie' => 'GPS=1; YSC=8O5Dqkbfe3I; VISITOR_INFO1_LIVE=ZTRgRstulEA; PREF=tz=Asia.Saigon',
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
