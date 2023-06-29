<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

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

        try{
            $call = Http::withHeaders([
                'lang' => 'vi',
                'gmt' => '420'
            ])->timeout(10)->retry(3, 1000, function (Exception $exception) {
                $code = $exception->getCode();
                $html = "API ERROR:\nStatus code: $code";
                $this->sendNotification($html);
                return false;
            })->post(env("API_LOCAMOS"), [
                "data" => "123",
                "sign_key" => "7690079b9d363394743c451089b4f508",
                "type" => "EMAIL",
                "user_id" => 111,
                "verify_token" => "DANGTINH", "full_name" => "DANGTINH"
            ]);
        }catch (Exception $exception){
            $this->sendNotification($exception->getMessage());
        }


        return 0;
    }

    private function sendNotification($text)
    {
        $chatId = "-902454915";
        $token = env('TOKEN_TELEGRAM');
        $api = "https://api.telegram.org/bot{$token}/sendMessage";
        $curl = Http::post($api, [
            "chat_id" => $chatId,
            "text" => ($text),
            "parse_mode" => "HTML"
        ]);
    }
}
