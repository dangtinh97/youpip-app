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
        $imagesData = ['https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-1.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-2.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-3.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-4.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-5.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-6.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-7.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-8.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-9.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-10.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-11.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-12.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-13.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-14.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-15.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-16.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-17.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-18.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-19.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-20.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-21.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-22.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-23.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-24.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-25.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-26-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-27.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-28.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-29.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-30.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-31-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-32-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-33.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-34.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-35-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-36-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-37.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-34.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-36.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-2.png','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-17.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-21.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-4.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-dep-hoi-an-4.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-dep-hoi-an-6.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-nha-trang-3.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-nha-trang-8.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-mien-tay-song-nuoc-6.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-mien-tay-song-nuoc-33.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-mien-tay-song-nuoc-13.jpg','https://wall.vn/wp-content/uploads/2020/04/tai-anh-sapa.jpg'];
        $imagesData = array_reverse($imagesData);
        try{
            $url = 'https://vnexpress.net/tin-tuc-24h';
            $body = Http::get($url)->body();

            /** @var simple_html_dom $dom */
            $dom = HtmlDomParser::str_get_html( $body );
            /** @var simple_html_dom_node $dataRange */
            $dataRange = $dom->find('#automation_TV0')[0];
            $childes = $dataRange->find('article');
            $output = [];

            foreach ($childes as $i=> $child){

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
                    'image' => $imagesData[$i],
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
