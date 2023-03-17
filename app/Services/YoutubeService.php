<?php

namespace App\Services;

use App\Enums\ELinkYoutube;
use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseError;
use App\Http\Response\ResponseSuccess;
use App\Repositories\VideoRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use YouTube\Exception\YouTubeException;
use YouTube\YouTubeDownloader;

class YoutubeService
{

    public function __construct(protected readonly VideoRepository $videoRepository){

    }

    /**
     * @param string $token
     *
     * @return \App\Http\Response\ApiResponse
     */
    public function listVideo(string $token): ApiResponse
    {
        $url = ELinkYoutube::BASE_URL->value.'/index?gl=VN';
        $body = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36',
            'accept-language' => 'vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5'
        ])->get($url)->body();
        preg_match('/ytInitialData =(.*?);</i', $body, $matches);
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
            if (!is_array($videoRender) || !$videoId = Arr::get($videoRender, 'videoId')) {
                continue;
            }
            $publishTime = (string)Arr::get($videoRender, 'publishedTimeText.simpleText');
            $timeText = (string)Arr::get($videoRender, 'lengthText.simpleText');
            if(!$publishTime || !$timeText) {
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
     */
    function linkVideo(string $videoId): ApiResponse
    {
        $youtube = new YouTubeDownloader();
        $url = ELinkYoutube::BASE_URL->value."/watch?v={$videoId}";
        $output = [];
        try {
            $downloadOptions = $youtube->getDownloadLinks($url);
            /** @var array $combine */
            if(!$combine = $downloadOptions->getCombinedFormats()){
                return new ResponseError();
            }
            /** @var \YouTube\Models\StreamFormat $last */
            $last = Arr::last($combine);
            $output = [
                'mime_type' => $last->mimeType,
                'url' => $last->url,
                'quality' => $last->quality
            ];
        } catch (YouTubeException $e) {
        }

        return !$output ? new ResponseError() : new ResponseSuccess($output);
    }

    /**
     * @param array $data
     *
     * @return void
     */
    private function saveVideo(array $videos):void
    {
        foreach ($videos as $video){

            $this->videoRepository->findAndModify([
                'video_id' => $video['video_id'] ?? ''
            ],[
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
}
