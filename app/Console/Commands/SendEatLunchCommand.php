<?php

namespace App\Console\Commands;

use App\Models\Log;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use MongoDB\BSON\ObjectId;

class SendEatLunchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:team-eat-lunch';

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
        $this->sendNotification("Đừng để tiền rơi, hãy điền thông tin người tham gia ăn ngày hôm nay\n".date('d/m/Y')."\n".route('team-eat-lunch'));

        return 0;
    }

    private function sendNotification(string $text)
    {
        $chatId = "-4063767770";
        $token = env('TOKEN_TELEGRAM');
        $api = "https://api.telegram.org/bot{$token}/sendMessage";
        $curl = Http::post($api, [
            "chat_id" => $chatId,
            "text" => "$text",
            "parse_mode" => "HTML"
        ]);
    }
}
