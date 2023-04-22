<?php

namespace App\Services;

use App\Repositories\LogRepository;

class ChatBotService
{
    public function __construct(
        protected readonly LogRepository $logRepository
    )
    {

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
        return [];
    }
}
