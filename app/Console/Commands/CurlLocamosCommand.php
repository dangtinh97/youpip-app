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
        $apiText = env('API_LOCAMOS');

        foreach (explode(",", $apiText) as $api) {
            $api .= "/reset-config-redis";

            try {
                $call = Http::withHeaders([
                    'lang' => 'vi',
                    'gmt' => '420'
                ])->timeout(10)->retry(3, 1000, function (Exception $exception) use ($api) {
                    $code = $exception->getCode();
                    $html = "API ERROR:\nStatus code: $code";
                    $this->sendNotification($api, $html);

                    return false;
                })->get($api);
                if ($call->status() !== 200) {
                    $this->sendNotification($api, $call->body());
                }
            } catch (Exception $exception) {
                $this->sendNotification($api, $exception->getMessage());
            }
        }

        return 0;
    }

    private function sendNotification(string $apiLink, string $text)
    {
        $chatId = "-902454915";
        $token = env('TOKEN_TELEGRAM');
        $api = "https://api.telegram.org/bot{$token}/sendMessage";
        $curl = Http::post($api, [
            "chat_id" => $chatId,
            "text" => "LINK API: $apiLink\n Error: $text",
            "parse_mode" => "HTML"
        ]);
    }
}
