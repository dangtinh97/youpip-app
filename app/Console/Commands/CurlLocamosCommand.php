<?php

namespace App\Console\Commands;

use App\Helper\ChatBotHelper;
use App\Models\Log;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use MongoDB\BSON\ObjectId;

class CurlLocamosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:curl-locamos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $apiText = env('API_LOCAMOS');
        $ids = Log::query()->where([
            'type' => 'curl-locamos',
        ])->orderByDesc('_id')->limit(100)->pluck('_id')->toArray();

        if(count($ids)>0){
            Log::query()->where([
                'type' => 'curl-locamos',
                '_id' => [
                    '$nin' => array_map(function($id){
                        return new ObjectId($id);
                    },$ids)
                ]
            ])->delete();
        }

        foreach (explode(",", $apiText) as $api) {
            $timeStart = time();
            try {
                $call = Http::withHeaders([
                    'lang' => 'vi',
                    'gmt' => '420'
                ])->timeout(10)->get($api);
                if (!$call->successful()) {
                    $this->sendNotification($api, $call->body());
                }
                Log::query()->create([
                    'type' => 'curl-locamos',
                    'data' => [
                        'status' => $call->status(),
                        'api' => $api,
                        'time' => time() - $timeStart
                    ]
                ]);
            } catch (Exception $exception) {
                Log::query()->create([
                    'type' => 'curl-locamos',
                    'data' => [
                        'status' => 500,
                        'api' => $api,
                        'time' => time() - $timeStart,
                    ],
                    "message" => $exception->getMessage()
                ]);
            }
            sleep(10);
        }

        return 0;
    }

    private function sendNotification(string $apiLink, string $text)
    {
        ChatBotHelper::sendToOwner("LINK API: $apiLink\n Error: $text");

//        $chatId = "-902454915";
//        $token = env('TOKEN_TELEGRAM');
//        $api = "https://api.telegram.org/bot{$token}/sendMessage";
//        $curl = Http::timeout(10)->post($api, [
//            "chat_id" => $chatId,
//            "text" => "LINK API: $apiLink\n Error: $text",
//            "parse_mode" => "HTML"
//        ]);
    }
}
