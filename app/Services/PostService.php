<?php

namespace App\Services;

use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseError;
use App\Http\Response\ResponseSuccess;
use App\Repositories\PostRepository;
use DOMDocument;
use Illuminate\Support\Facades\Http;
use KubAT\PhpSimple\HtmlDomParser;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use simple_html_dom\simple_html_dom;
use simple_html_dom\simple_html_dom_node;

class PostService
{
    public function __construct(protected readonly PostRepository $postRepository)
    {
    }

    public function index(string $lastOid):ApiResponse
    {
        return $this->crawlData();
    }

    public function crawlData(){
        try{
            $url = 'https://vnexpress.net/tin-tuc-24h';
            $body = Http::get($url)->body();

            /** @var simple_html_dom $dom */
            $dom = HtmlDomParser::str_get_html( $body );
            /** @var simple_html_dom_node $dataRange */
            $dataRange = $dom->find('#automation_TV0')[0];
            $childes = $dataRange->find('article');
            $output = [];
            foreach ($childes as $child){
                /** @var simple_html_dom_node $child */

                /** @var simple_html_dom_node $timeCount */
                $timeCount = $child->find('.time-count > span');
                if(!$timeCount){
                    continue;
                }
                $datePublished = new UTCDateTime(strtotime($timeCount[0]->attr['datetime'])*1000);
                $title = $child->find('.title-news > a')[0]->text();

                $shortContent = $child->find('.description > a')[0]->text();
                $image = '';
                if(count($child->children)==4){
                    $images = $child->find('.thumb-art > a > picture > img');
                    if($images){
                        $image = $images[0]->attr['data-src'] ?? $images[0]->attr['src'];
                    }
                }
                $output[] = [
                    'user_id' => 8,
                    'full_name' => 'VnExpress',
                    'image' => $image,
                    'title' => $title,
                    'short_content' => $shortContent,
                    'content' => '',
                    'time_published' => date('Y-m-d H:i:s',$datePublished->toDateTime()->getTimestamp()),
                    'post_oid' => (string)(new ObjectId())
                ];
            }

            return new ResponseSuccess([
                'list' => $output
            ]);
        }catch (\Exception $exception){
            return new ResponseError();
        }

    }
}
