<?php

namespace App\Services;

use App\Enums\EBlockChatBot;
use App\Enums\EStatusChatBot;
use App\Helper\ChatBotHelper;
use App\Models\CbUser;
use App\Repositories\Chatbot\ChatGptRepository;
use App\Repositories\Chatbot\UserRepository as CbRepository;
use App\Repositories\ConfigRepository;
use App\Repositories\LogRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use MongoDB\BSON\UTCDateTime;
use OpenAI;

class ChatBotService
{
    const CONNECT = "CONNECT";
    const DISCONNECT = "DISCONNECT";
    const MENU = "MENU";
    const CHAT_GPT = 'CHAT_GPT';
    const END_CHAT_GPT = 'END_CHAT_GPT';
    const RESET_CHAT_GPT = 'RESET_CHAT_GPT';

    /**
     * @var string
     */
    private string $sendFrom;

    /**
     * @var \App\Models\CbUser
     */
    private CbUser $user;

    private ?CbUser $connectWith = null;

    /**
     * @var array
     */
    private array $messaging;

    /**
     * @param \App\Repositories\LogRepository          $logRepository
     * @param \App\Repositories\Chatbot\UserRepository $cbUserRepository
     */
    public function __construct(
        protected readonly LogRepository $logRepository,
        protected readonly CbRepository $cbUserRepository,
        protected readonly ChatGptRepository $chatGptRepository,
        protected readonly ConfigRepository $configRepository
    ) {
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function onWebhook(array $data): array
    {
        $this->logRepository->create([
            'type' => 'CHATBOT_WEBHOOK',
            'data' => $data
        ]);

        $object = Arr::get($data, 'object');
        if ($object !== "page") {
            return $this->responseSelf("400|Hệ thống gián đoạn.");
        }

        $sendFrom = (string)Arr::get($data, 'entry.0.messaging.0.sender.id', '');
        $this->sendFrom = $sendFrom;
        /** @var \App\Models\CbUser|null $user */
        $user = $this->cbUserRepository->first([
            'fbid' => $sendFrom,
        ]);

        if (!$user instanceof CbUser) {
            /** @var CbUser $user */
            $user = $this->cbUserRepository->create([
                'fbid' => $sendFrom,
                'block' => EBlockChatBot::DEFAULT->value,
                'status' => EStatusChatBot::FREE->value,
                'id' => $this->cbUserRepository->getId()
            ]);
        }

        $user->update([
            'time_latest' => new UTCDateTime(time() * 1000)
        ]);

        $this->user = $user;

        $messaging = (array)Arr::get($data, 'entry.0.messaging.0');
        $this->messaging = $messaging;
        $this->findConnect();

        return match (true) {
            !is_null(Arr::get($messaging, 'message.quick_reply.payload')) => $this->onQuickReply(),
            !is_null(Arr::get($messaging, 'message.text')) => $this->onMessageText(),
            !is_null(Arr::get($messaging, 'postback')) => $this->onPostBack(),
            !is_null(Arr::get($messaging, 'message.attachments')) => $this->onAttachment(),
            default => $this->responseSelf("201|Hệ thống gián đoạn.")
        };
    }

    private function onAttachment(): array
    {
        return $this->responseSelf("Hiện tại chưa hỗ trợ gửi file đa phương tiện\nVui lòng thử lại sau.\nQC: #YouPiP app xem youtube không quảng cáo , hỗ trợ xem trong nền...");
    }

    private function onQuickReply(): array
    {
        $payload = Arr::get($this->messaging, 'message.quick_reply.payload');
        if ($payload === self::CONNECT) {
            return $this->connect();
        }

        if ($payload === self::DISCONNECT) {
            return $this->disconnect();
        }

        if ($payload === self::MENU) {
            return $this->menu();
        }

        return [];
    }

    private function menu(): array
    {
        $urlQc = "https://youpip.net/storage/202304/1682189714no-adsjpg.jpg";
        $generic = ChatBotHelper::generic("1 phút dành cho quảng cáo.\n#YouPip",
            "- Xem phim không quảng cáo.\n- Xem trong nền\n- ChatGpt", [
                [
                    'type' => 'web_url',
                    'url' => 'https://youpip.net',
                    'title' => 'Tải ứng dụng'
                ],
                [
                    'type' => 'postback',
                    'title' => '💬 Tìm người lạ',
                    'payload' => self::CONNECT
                ],
                [
                    'type' => 'postback',
                    'title' => 'Chat GPT',
                    'payload' => self::CHAT_GPT
                ]
            ], $urlQc);
        $body = $this->body($this->sendFrom, $generic);
        $this->sendMessage($body);

        return [];
    }

    /**
     * @return array
     */
    private function onPostBack(): array
    {
        $payload = Arr::get($this->messaging, 'postback.payload');
        if ($payload === self::CONNECT) {
            return $this->connect();
        }

        if ($payload === self::DISCONNECT) {
            return $this->disconnect();
        }

        if ($payload === self::MENU) {
            return $this->menu();
        }

        if ($payload === self::CHAT_GPT) {
            return $this->chatGPT(EBlockChatBot::CHAT_GPT->value);
        }

        if ($payload === self::END_CHAT_GPT) {
            return $this->chatGPT(EBlockChatBot::DEFAULT->value);
        }

        if ($payload === self::RESET_CHAT_GPT) {
            return $this->resetChatGpt();
        }

        return [];
    }

    /**
     * @return array
     */
    private function resetChatGpt(): array
    {
        $this->chatGptRepository->deleteWhere([
            'user_id' => $this->user->id
        ]);

        return [];
    }

    /**
     * @param string $block
     *
     * @return array
     */
    private function chatGPT(string $block): array
    {
        $this->user->update([
            'block' => $block
        ]);

        if ($block === EBlockChatBot::CHAT_GPT->value) {
            return $this->responseSelf(ChatBotHelper::quickReply("Xin chào! Tôi là ChatGPT. Bạn cần tôi giúp gì?", [
                [
                    'title' => 'Dừng lại.',
                    'payload' => self::END_CHAT_GPT
                ]
            ]));
        }

        return $this->responseSelf(ChatBotHelper::quickReply("Đoạn chat đã dừng lại, tiếp tục trò chuyện với người lạ nào.",
            [
                [
                    'title' => '❌ Rời chat!',
                    'payload' => self::DISCONNECT
                ],
                [
                    'title' => '📁 Chức năng',
                    'payload' => self::MENU
                ]
            ]));
    }

    /**
     * @return array
     */
    private function connect(): array
    {
        $status = $this->user->status;

        $messageResponseMe = match ($status) {
            EStatusChatBot::FREE->value => 'Bạn sẽ được kết nối với một người ngay khi chúng tôi tìm được một người phù hợp. Chúc bạn may mắn!',
            EStatusChatBot::WAIT->value => 'Chúng tớ đang tìm kiếm một người thích hợp để kết nối với bạn. Bạn có thể chờ một chút nữa không?',
            EStatusChatBot::BUSY->value => 'Bạn muốn tiếp tục trò chuyện với người mà mình đang kết nối không?',
            default => "Có gì đó sai sai.",
        };

        if ($status === EStatusChatBot::BUSY->value) {
            return $this->responseSelf(ChatBotHelper::quickReply($messageResponseMe, [
                [
                    'title' => '❌ Rời chat!',
                    'payload' => self::DISCONNECT
                ]
            ]));
        }

        /** @var CbUser|null $findWaitConnect */
        $findWaitConnect = $this->cbUserRepository->first([
            'fbid' => [
                '$ne' => $this->sendFrom,
            ],
            'status' => EStatusChatBot::WAIT,
            'time_latest' => [
                '$gte' => new UTCDateTime(strtotime('-20 hours') * 1000)
            ]
        ]);

        $this->user->update([
            'status' => EStatusChatBot::WAIT->value,
            'block' => EBlockChatBot::DEFAULT->value
        ]);

        if (!$findWaitConnect instanceof CbUser) {
            return $this->responseSelf(ChatBotHelper::quickReply($messageResponseMe, [
                [
                    'title' => '❌ Rời chat!',
                    'payload' => self::DISCONNECT
                ],
                [
                    'title' => '📁 Chức năng',
                    'payload' => self::MENU
                ]
            ]));
        }

        $findWaitConnect->update([
            'status' => EStatusChatBot::BUSY->value,
            'fbid_connect' => $this->sendFrom,
            'block' => EBlockChatBot::DEFAULT->value
        ]);
        $this->user->update([
            'status' => EStatusChatBot::BUSY->value,
            'fbid_connect' => $findWaitConnect->fbid
        ]);

        $body = $this->body($findWaitConnect->fbid,
            ChatBotHelper::quickReply("Có một người vừa kết nối với bạn, trò chuyện ngay nhé.", [
                [
                    'title' => '❌ Rời chat!',
                    'payload' => self::DISCONNECT
                ],
                [
                    'title' => '📁 Chức năng',
                    'payload' => self::MENU
                ]
            ]));

        $this->sendMessage($body);

        return $this->responseSelf(ChatBotHelper::quickReply("Chúng tớ đã tìm cho bạn được một người, trò chuyện ngay nhé.",
            [
                [
                    'title' => '📲 Kết nối',
                    'payload' => self::CONNECT
                ],
                [
                    'title' => '❌ Rời chat',
                    'payload' => self::DISCONNECT
                ],
                [
                    'title' => '📁 Chức năng',
                    'payload' => self::MENU
                ]
            ]));
    }

    /**
     * @return array
     */
    private function onMessageText(): array
    {
        $text = Arr::get($this->messaging, 'message.text');
        $text = ChatBotHelper::removeBadWord($text);
        if (in_array($text, ['#ketnoi', '#batdau', '#timkiem', '#timnguoila'])) {
            return $this->connect();
        }
        if (in_array($text, ['#ngatketnoi', '#pipi', '#end', '#endchat'])) {
            return $this->disconnect();
        }
        if (in_array($text, ['#menu', '#help'])) {
            return $this->menu();
        }

        if (in_array($text, ['#resetchat'])) {
            return $this->connect();
        }

        if ($this->user->block === EBlockChatBot::CHAT_GPT->value) {
            return $this->onChatGpt($text);
        }

        if (!$this->connectWith instanceof CbUser) {
            return [];
        }
        $text = ChatBotHelper::removeBadWord($text);
        $this->sendMessage(
            $this->body($this->connectWith?->fbid, $text)
        );

        return [];
    }

    /**
     * @param string $text
     *
     * @return array[]|\array[][]|string[]|\string[][]
     */
    private function onChatGpt(string $text): array
    {
        $messages = $this->user->messagesChatGpt;
        if ($messages->count() >= 20 && $this->sendFrom !== '1343954529053153') {
            $this->user->update([
                'block' => EBlockChatBot::DEFAULT->value,
            ]);

            return $this->responseSelf(ChatBotHelper::quickReply("Đã hết thời gian thử nghiệm chatgpt.", [
                [
                    'title' => '📲 Kết nối',
                    'payload' => self::CONNECT
                ],
                [
                    'title' => '❌ Rời chat',
                    'payload' => self::DISCONNECT
                ],
                [
                    'title' => '📁 Chức năng',
                    'payload' => self::MENU
                ]
            ]));
        }

        /** @var \App\Models\Config $config */
        $config = $this->configRepository->first([
            'type' => 'OPENAI'
        ]);

        $data = $config->data ?? [];
        $key = env('OPENAI_KEY', '');
        $keys = explode(",", Arr::get($data, 'api_key', $key));
        shuffle($keys);
        $client = OpenAI::client($key = $keys[0] ?? $key);
        $messages = $messages->map(function ($item) {
            /** @var \App\Models\CBChatGpt $item */
            return [
                'role' => $item->role,
                'content' => $item->message
            ];
        })->add([
            'role' => 'user',
            'content' => $text
        ])->toArray();

        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages
        ])->toArray();
        $message = Arr::get($response, 'choices.0.message.content');

        if (is_null($message)) {
            $this->chatGptRepository->insert([
                [
                    'user_id' => $this->user->id,
                    'role' => 'user',
                    'content' => $text
                ],
                [
                    'user_id' => $this->user->id,
                    'role' => 'assistant',
                    'content' => $text
                ]
            ]);
        }

        $quicks = [];

        if ($this->sendFrom === '1343954529053153') {
            $quicks[] = [
                'title' => 'Đoạn chat mới',
                'payload' => self::RESET_CHAT_GPT
            ];
        }

        $quicks[] = [
            'title' => 'Dừng lại',
            'payload' => self::END_CHAT_GPT
        ];

        $quicks[] = [
            'title' => 'Tìm người lạ',
            'payload' => self::CONNECT
        ];

        return $this->responseSelf(ChatBotHelper::quickReply($message ?? "Xin lỗi!\nTôi đang gặp sự cố... :(",
            $quicks));
    }

    /**
     * @param string       $toFbId
     * @param array|string $message
     *
     * @return array
     */
    private function body(string $toFbId, array|string $message = ''): array
    {
        if (is_string($message)) {
            $message = [
                'text' => $message
            ];
        }

        return [
            'recipient' => [
                'id' => $toFbId
            ],
            'messaging_type' => 'RESPONSE',
            'message' => $message
        ];
    }

    /**
     * @param array|string $text
     *
     * @return \array[][]|\string[][]
     */
    private function responseSelf(array|string $content): array
    {
        if (is_string($content)) {
            $content = [
                'text' => $content
            ];
        }

        $body = $this->body($this->sendFrom, $content);
        $this->sendMessage($body);

        return [
            'message' => $content
        ];
    }

    private function findConnect()
    {
        if (!$connectWithFbId = $this->user->fbid_connect) {
            return;
        }
        $this->connectWith = $this->cbUserRepository->first([
            'fbid' => $connectWithFbId
        ]);
    }

    /**
     * @param array $body
     *
     * @return bool
     */
    private function sendMessage(array $body): bool
    {
        try {
            $url = 'https://graph.facebook.com/v16.0/'.env('PAGE_ID', '482929468728139')
                .'/messages?access_token='.env('PAGE_ACCESS_TOKEN');
            $send = Http::withBody(json_encode($body), 'application/json')
                ->post($url);
            $json = $send->json();
            if ($send->status() === 200 && Arr::get($json, 'message_id')) {
                return true;
            }

            $this->logRepository->create([
                'type' => 'RESPONSE_SEND_WEBHOOK_ERROR',
                'data' => [
                    'response' => $json,
                    'body' => $body
                ]
            ]);

            return false;
        } catch (\Exception $exception) {
            $this->logRepository->create([
                'type' => 'RESPONSE_SEND_WEBHOOK_ERROR',
                'data' => [
                    'response' => $exception,
                    'body' => $body
                ]
            ]);

            return false;
        }
    }

    /**
     * @return array[]|\array[][]|string[]|\string[][]
     */
    private function disconnect(): array
    {
        $status = $this->user->status;
        if ($status === EStatusChatBot::BUSY->value) {
            $fbIdConnect = $this->user->fbid_connect;
            $this->cbUserRepository->update([
                'fbid' => $fbIdConnect
            ], [
                'status' => EStatusChatBot::FREE->value,
                'fbid_connect' => null,
            ]);

            $body = $this->body($fbIdConnect,
                ChatBotHelper::quickReply("Người lạ đã ngắt kết nối với bạn!\nNhấn tìm kiếm để bắt đầu cuộc trò chuyện mới.",
                    [
                        [
                            'title' => '📲 Kết nối',
                            'payload' => self::CONNECT
                        ],
                        [
                            'title' => '📁 Chức năng',
                            'payload' => self::MENU
                        ]
                    ]));

            $this->sendMessage($body);
        }

        $messageResponseMe = match ($status) {
            EStatusChatBot::FREE->value => 'Bạn chưa được kết nối với ai.',
            EStatusChatBot::WAIT->value => 'Bạn đã rời hàng đợi.',
            EStatusChatBot::BUSY->value => 'Đã ngắt kết nối với người lạ.',
            default => "Có gì đó sai sai.",
        };

        $this->user->update([
            'status' => EStatusChatBot::FREE->value,
            'fbid_connect' => null,
            'block' => EBlockChatBot::DEFAULT->value
        ]);

        return $this->responseSelf(ChatBotHelper::quickReply($messageResponseMe, [
            [
                'title' => '📲 Tìm kiếm!',
                'payload' => self::CONNECT
            ],
            [
                'title' => '📁 Chức năng',
                'payload' => self::MENU
            ]
        ]));
    }
}
