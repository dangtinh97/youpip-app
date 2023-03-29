<?php

namespace App\Services;

use App\Enums\EPostViewMode;
use App\Enums\EStatusApi;
use App\Http\Response\ApiResponse;
use App\Http\Response\ResponseError;
use App\Http\Response\ResponseSuccess;
use App\Repositories\AttachmentRepository;
use App\Repositories\PostRepository;
use DOMDocument;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use KubAT\PhpSimple\HtmlDomParser;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use simple_html_dom\simple_html_dom;
use simple_html_dom\simple_html_dom_node;

class PostService
{
    public function __construct(
        protected readonly PostRepository $postRepository,
        protected readonly AttachmentRepository $attachmentRepository
    )
    {
    }

    public function index(?string $lastOid,?int $userId):ApiResponse
    {
        $posts =$this->postRepository->index($lastOid,$userId);
        $imagesData = ['https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-1.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-2.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-3.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-4.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-5.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-6.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-7.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-8.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-9.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-10.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-11.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-12.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-13.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-14.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-15.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-16.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-17.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-18.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-19.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-20.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-21.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-22.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-23.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-24.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-25.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-26-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-27.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-28.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-29.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-30.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-31-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-32-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-33.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-34.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-35-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-36-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-37.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-34.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-36.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-2.png','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-17.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-21.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-4.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-dep-hoi-an-4.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-dep-hoi-an-6.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-nha-trang-3.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-nha-trang-8.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-mien-tay-song-nuoc-6.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-mien-tay-song-nuoc-33.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-mien-tay-song-nuoc-13.jpg','https://wall.vn/wp-content/uploads/2020/04/tai-anh-sapa.jpg'];
        shuffle($imagesData);
        $maps = $posts->map(function ($post,$index) use ($imagesData){
            /** @var \App\Models\Post $post */

            $countAction = $post->count_action ?? [];

            if($images =$post->getAttribute('image')){
                $image = Storage::disk($images[0]['disk'])->url($images[0]['path']);

            }else{
                $image = $index<count($imagesData)? $imagesData[$index] : $imagesData[0];
            }
            $userName = $post->users ?? [];

            return [
                'user_id' => $post->user_id,
                'full_name' => Arr::get($userName,'0.full_name','Người dùng'),
                'image' => $image,
                'content' => $post->content,
                'post_oid' => $post->_id,
                'time' => date($post->created_at),
                'total_comment' => Arr::get($countAction,'comment',0),
                'total_like' => Arr::get($countAction,'like',0),
                'liked' => false,
                'post_id' => $post->id
            ];

        });

        return new ResponseSuccess([
            'list' => $maps->toArray()
        ]);
    }

    public function crawlData(){
        $imagesData = ['https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-1.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-2.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-3.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-4.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-5.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-6.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-7.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-8.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-9.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-10.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-11.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-12.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-13.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-14.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-15.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-16.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-17.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-18.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-19.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-20.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-21.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-22.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-23.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-24.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-25.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-26-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-27.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-28.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-29.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-30.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-31-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-32-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-33.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-34.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-35-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-36-scaled.jpg','https://wall.vn/wp-content/uploads/2020/04/anh-dep-viet-nam-37.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-34.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-36.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-2.png','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-17.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-21.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-son-doong-4.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-dep-hoi-an-4.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-dep-hoi-an-6.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-nha-trang-3.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-nha-trang-8.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-mien-tay-song-nuoc-6.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-mien-tay-song-nuoc-33.jpg','https://wall.vn/wp-content/uploads/2020/04/hinh-anh-mien-tay-song-nuoc-13.jpg','https://wall.vn/wp-content/uploads/2020/04/tai-anh-sapa.jpg'];
        shuffle($imagesData);





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

            if(count($output)===0){
                return new ResponseError(EStatusApi::NO_CONTENT->value);
            }

            return new ResponseSuccess([
                'list' => $output
            ]);
        }catch (\Exception $exception){

            return new ResponseError();
        }

    }

    public function create(string $content,int $attachmentId):ApiResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        /** @var \App\Models\Post $create */
        $create = $this->postRepository->create([
            'user_id' => $user->id ,
            'id' => $this->postRepository->getId(),
            'content' => $content,
            'attachment_id' => $attachmentId,
            'count_action' => [],
            'view_mode' => EPostViewMode::PUBLIC->value
        ]);
        if($attachmentId!=0){
            $this->attachmentRepository->setUse($attachmentId);
        }

        return new ResponseSuccess([
            'post_oid' => $create->_id
        ]);
    }
}
