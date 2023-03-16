<?php

namespace App\Services;

use App\Enums\ELinkYoutube;
use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseError;
use App\Http\Response\ResponseSuccess;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use YouTube\Exception\YouTubeException;
use YouTube\YouTubeDownloader;

class YoutubeService
{

    public function __construct(){

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
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36'
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

            $output[] = [
                'video_id' => $videoId,
                'thumbnail' => (string)Arr::get($videoRender, 'thumbnail.thumbnails.0.url'),
                'title' => (string)Arr::get($videoRender, 'title.runs.0.text'),
                'time_text' => (string)Arr::get($videoRender, 'lengthText.simpleText'),
                'view_count_text' => (string)Arr::get($videoRender, 'viewCountText.simpleText'),
                'chanel_name' => (string)Arr::get($videoRender, 'longBylineText.runs.0.text'),
                'chanel_url' => (string)Arr::get($videoRender,
                    'longBylineText.runs.0.navigationEndpoint.browseEndpoint.canonicalBaseUrl'),
                'published_time' => (string)Arr::get($videoRender, 'publishedTimeText.simpleText')
            ];
        }
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
}
