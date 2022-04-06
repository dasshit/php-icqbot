<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use GuzzleHttp\Client;

class Bot
{
    private string $token;
    private string $api_url;
    private string $parse_mode;
    private int $lastEventId = 1;
    private int $pollTime;

    private GuzzleHttp\Client $session;
    public Logger $logger;

    public function __construct(
        string $token,
        string $api_url = "https://api.icq.net/bot/v1",
        string $parse_mode = "HTML",
        int $pollTime = 30,
        int $log_level = Logger::INFO
    )
    {
        $this->api_url = $api_url;
        $this->token = $token;
        $this->pollTime = $pollTime;
        $this->parse_mode = $parse_mode;

        $this->session = new Client([
            'base_uri' => $api_url,
        ]);

        $this->logger = new Logger('bot');
        $this->logger->pushHandler(new StreamHandler('./test_log.log', $log_level));  //

        $this->logger->info("Bot started: $token -> $api_url");
    }

    public function __destruct()
    {
        $this->logger->info("Bot finished: " . $this->token);
    }

    private function get(string $path, array $query): array
    {
        $query += [ "token" => $this->token ];

        $filtered_query = array_filter($query, function ($elem){
            return !empty($elem) and $elem != "null";
        });

        $this->logger->debug("[GET] /bot/v1" . $path);

        $result = $this->session->get("/bot/v1" . $path, [
            "query" => $filtered_query
        ])->getBody();

        $this->logger->debug($result);

        return json_decode($result, true);
    }

    private function post(string $path, array $query, string $file_path): array
    {
        $query += [ "token" => $this->token ];

        $filtered_query = array_filter($query, function ($elem){
            return !empty($elem) and $elem != "null";
        });

        $this->logger->debug("[POST] /bot/v1" . $path);

        $result = $this->session->post("/bot/v1" . $path, [
            "query" => $filtered_query,
            "multipart" => [[
                "name" => "file",
                "filename" => basename($file_path),
                "contents" => file_get_contents($file_path)
            ]]
        ])->getBody();

        $this->logger->debug($result);

        return json_decode($result, true);
    }

    public function sendText(
        string $chatId,
        string $text,
        string|int|null $replyMsgId = NULL,
        string|null $forwardChatId = NULL,
        array|null $forwardMsgId = NULL,
        array|null $inlineKeyboardMarkup = NULL,
        object|null $format = NULL,
        string|null $parseMode = NULL,
    ): array
    {
        if ($parseMode == NULL) {
            $parseMode = $this->parse_mode;
        }

        return $this->get("/messages/sendText", [
            "chatId" => $chatId,
            "text" => $text,
            "replyMsgId" => $replyMsgId,
            "forwardChatId" => $forwardChatId,
            "forwardMsgId" => json_encode($forwardMsgId),
            "inlineKeyboardMarkup" => json_encode($inlineKeyboardMarkup),
            "format" => $format,
            "parseMode" => $parseMode
        ]);
    }

    public function sendFile (
        string $chatId,
        string|NULL $fileId = NULL,
        string|NULL $filePath = NULL,
        string|NULL $caption = NULL,
        array|NULL $replyMsgId = NULL,
        string|NULL $forwardChatId = NULL,
        array|NULL $forwardMsgId = NULL,
        array|NULL $inlineKeyboardMarkup = NULL,
        object|NULL $format = NULL,
        string|NULL $parseMode = NULL,
    )
    {
        if ($parseMode == NULL) {
            $parseMode = $this->parse_mode;
        }

        if ($filePath != NULL) {
            return $this->post("/messages/sendFile",
                [
                    "chatId" => $chatId,
                    "fileId" => $fileId,
                    "caption" => $caption,
                    "replyMsgId" => $replyMsgId,
                    "forwardChatId" => $forwardChatId,
                    "forwardMsgId" => json_encode($forwardMsgId),
                    "inlineKeyboardMarkup" => json_encode($inlineKeyboardMarkup),
                    "format" => $format,
                    "parseMode" => $parseMode
                ],
                $filePath
            );
        }

        return $this->get("/messages/sendFile", [
            "chatId" => $chatId,
            "fileId" => $fileId,
            "caption" => $caption,
            "replyMsgId" => $replyMsgId,
            "forwardChatId" => $forwardChatId,
            "forwardMsgId" => json_encode($forwardMsgId),
            "inlineKeyboardMarkup" => json_encode($inlineKeyboardMarkup),
            "format" => $format,
            "parseMode" => $parseMode
        ]);
    }

    public function sendVoice (
        string $chatId,
        string|NULL $fileId = NULL,
        string|NULL $filePath = NULL,
        string|NULL $caption = NULL,
        array|NULL $replyMsgId = NULL,
        string|NULL $forwardChatId = NULL,
        array|NULL $forwardMsgId = NULL,
        array|NULL $inlineKeyboardMarkup = NULL,
        object|NULL $format = NULL,
        string|NULL $parseMode = NULL,
    )
    {
        if ($parseMode == NULL) {
            $parseMode = $this->parse_mode;
        }

        if ($filePath != NULL) {
            return $this->post("/messages/sendVoice",
                [
                    "chatId" => $chatId,
                    "fileId" => $fileId,
                    "caption" => $caption,
                    "replyMsgId" => $replyMsgId,
                    "forwardChatId" => $forwardChatId,
                    "forwardMsgId" => json_encode($forwardMsgId),
                    "inlineKeyboardMarkup" => json_encode($inlineKeyboardMarkup),
                    "format" => $format,
                    "parseMode" => $parseMode
                ],
                $filePath
            );
        }

        return $this->get("/messages/sendVoice", [
            "chatId" => $chatId,
            "fileId" => $fileId,
            "caption" => $caption,
            "replyMsgId" => $replyMsgId,
            "forwardChatId" => $forwardChatId,
            "forwardMsgId" => json_encode($forwardMsgId),
            "inlineKeyboardMarkup" => json_encode($inlineKeyboardMarkup),
            "format" => $format,
            "parseMode" => $parseMode
        ]);
    }

    public function editText(
        string $chatId,
        string|int $msgId,
        string $text,
        string|int|null $replyMsgId = NULL,
        string|null $forwardChatId = NULL,
        array|null $forwardMsgId = NULL,
        array|null $inlineKeyboardMarkup = NULL,
        object|null $format = NULL,
        string|null $parseMode = NULL,
    ): array
    {
        $this->logger->info("$chatId: $text");

        if ($parseMode == NULL) {
            $parseMode = $this->parse_mode;
        }

        return $this->get("/messages/editText", [
            "chatId" => $chatId,
            "msgId" => $msgId,
            "text" => $text,
            "replyMsgId" => $replyMsgId,
            "forwardChatId" => $forwardChatId,
            "forwardMsgId" => json_encode($forwardMsgId),
            "inlineKeyboardMarkup" => json_encode($inlineKeyboardMarkup),
            "format" => $format,
            "parseMode" => $parseMode
        ]);
    }

    public function deleteMessage (
        string $chatId,
        string|int $msgId
    ): array
    {
        return $this->get("/messages/deleteMessages", [
            "chatId" => $chatId,
            "msgId" => $msgId
        ]);
    }

    public function answerCallbackQuery (
        string $queryId,
        string|NULL $text = NULL,
        bool $showAlert = false,
        string|NULL $url = NULL,
    ): array
    {
        return $this->get("/messages/answerCallbackQuery", [
            "queryId" => $queryId,
            "text" => $text,
            "showAlert" => $showAlert,
            "url" => $url
        ]);
    }

    public function createChat (
        string $name,
        string|NULL $about = NULL,
        string|NULL $rules = NULL,
        array $members = [],
        bool $public = false,
        string $defaultRole = 'member',
        bool $joinModeration = true
    ): array
    {
        return $this->get("/chats/createChat", [
            "name" => $name,
            "about" => $about,
            "rules" => $rules,
            "members" => json_encode($members),
            "public" => $public,
            "defaultRole" => $defaultRole,
            "joinModeration" => $joinModeration
        ]);
    }

    public function chatMembersAdd (
        string $chatId,
        array $members,
    ): array
    {
        return $this->get("/chats/members/add", [
            "chatId" => $chatId,
            "members" => json_encode($members)
        ]);
    }

    public function chatMembersDelete (
        string $chatId,
        array $members,
    ): array
    {
        return $this->get("/chats/members/delete", [
            "chatId" => $chatId,
            "members" => json_encode($members)
        ]);
    }

    public function chatsSendAction (
        string $chatId,
        string $action
    ): array
    {
        return $this->get("/chats/sendActions", [
            "chatId" => $chatId,
            "actions" => $action
        ]);
    }

    public function chatGetInfo(
        string $chatId,
    ): array
    {
        return $this->get("/chats/getInfo", [
            "chatId" => $chatId
        ]);
    }

    public function chatGetAdmins (
        string $chatId,
    ): array
    {
        return $this->get("/chats/getAdmins", [
            "chatId" => $chatId
        ]);
    }

    public function chatGetMembers (
        string $chatId,
    ): array
    {
        return $this->get("/chats/getMembers", [
            "chatId" => $chatId
        ]);
    }

    public function chatGetBlockedUsers (
        string $chatId,
    ): array
    {
        return $this->get("/chats/getBlockedUsers", [
            "chatId" => $chatId
        ]);
    }

    public function chatGetPendingUsers (
        string $chatId,
    ): array
    {
        return $this->get("/chats/getPendingUsers", [
            "chatId" => $chatId
        ]);
    }

    public function chatsBlockUser (
        string $chatId,
        string $userId,
        bool $delLastMessages
    ): array
    {
        return $this->get("/chats/blockUser", [
            "chatId" => $chatId,
            "userId" => $userId,
            "delLastMessages" => $delLastMessages
        ]);
    }

    public function chatsUNblockUser (
        string $chatId,
        string $userId,
    ): array
    {
        return $this->get("/chats/unblockUser", [
            "chatId" => $chatId,
            "userId" => $userId
        ]);
    }

    public function chatsResolvePending (
        string $chatId,
        bool $approve,
        string|NULL $userId = NULL,
        bool|NULL $everyone = NULL
    ): array
    {
        if ($userId != NULL) {
            return $this->get("/chats/resolvePending", [
                "chatId" => $chatId,
                "approve" => $approve,
                "userId" => $userId
            ]);
        }
        elseif ($everyone != NULL) {
            return $this->get("/chats/resolvePending", [
                "chatId" => $chatId,
                "approve" => $approve,
                "everyone" => $everyone
            ]);
        }
        else {
            throw new Exception("userId or everyone must be provided");
        }
    }

    public function chatsSetTitle (
        string $chatId,
        string $title,
    ): array
    {
        return $this->get("/chats/setTitle", [
            "chatId" => $chatId,
            "title" => $title
        ]);
    }

    public function chatsAvatarSet (
        string $chatId,
        string $image_path,
    ): array
    {
        return $this->post("/chats/avatar/set", [
            "chatId" => $chatId
        ],
        $image_path);
    }

    public function chatsSetAbout (
        string $chatId,
        string $about,
    ): array
    {
        return $this->get("/chats/setTitle", [
            "chatId" => $chatId,
            "about" => $about
        ]);
    }

    public function chatsSetRules (
        string $chatId,
        string $rules,
    ): array
    {
        return $this->get("/chats/setTitle", [
            "chatId" => $chatId,
            "rules" => $rules
        ]);
    }

    public function pinMessage (
        string $chatId,
        string|int $msgId
    ): array
    {
        return $this->get("/chats/pinMessage", [
           "chatId" => $chatId,
           "msgId"=> $msgId
        ]);
    }

    public function unpinMessage (
        string $chatId,
        string|int $msgId
    ): array
    {
        return $this->get("/chats/unpinMessage", [
            "chatId" => $chatId,
            "msgId"=> $msgId
        ]);
    }

    public function filesGetInfo (
        string $fileId
    ): array
    {
        return $this->get("/files/getInfo", [
            "fileId" => $fileId
        ]);
    }

    public function eventsGet (): array
    {
        $events = $this->get("/events/get", [
            "lastEventId" => $this->lastEventId,
            "pollTime" => $this->pollTime
        ]);

        $lastEvent = end($events["events"]); reset($events["events"]);

        $this->lastEventId = $lastEvent["eventId"];

        return $events;
    }
}