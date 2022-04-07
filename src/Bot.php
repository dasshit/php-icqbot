<?php
namespace Dasshit\IcqBot {

    use Exception;
    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;

    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\GuzzleException;
    use GuzzleHttp\Exception\RequestException;
    use TypeError;

    /**
     * Class Bot
     * @package Dasshit\IcqBot
     */
    class Bot
    {
        private string $token;
        private string $parse_mode;
        private int $lastEventId = 1;
        private int $pollTime;

        private array $eventCheckers = [];
        private array $commands = [];

        private Client $session;
        public Logger $logger;

        /**
         * @param string token Токен бота
         * @param string api_url URL bot API
         * @param string parse_mode Режим разбора форматирования текстового сообщения
         * @param int pollTime Максимальная длительность polling-запроса событий
         * @param int log_level Уровень логирования
         * @param string log_path Путь до файла с логами
         */
        public function __construct(
            string $token,
            string $api_url = "https://api.icq.net/bot/v1",
            string $parse_mode = "HTML",
            int    $pollTime = 30,
            int    $log_level = Logger::INFO,
            string $log_path = "./test_log.log"
        )
        {
            $this->token = $token;
            $this->pollTime = $pollTime;
            $this->parse_mode = $parse_mode;

            $this->session = new Client([
                'base_uri' => $api_url,
            ]);

            $this->logger = new Logger('bot');
            $this->logger->pushHandler(new StreamHandler($log_path, $log_level));  //

            $this->logger->info("Bot started: $token -> $api_url");
        }

        public function __destruct()
        {
            $this->logger->info("Bot finished: " . $this->token);
        }

        /**
         * @param string $path Path запроса к API
         * @param array $query Query параметры запроса
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        private function get(string $path, array $query): array
        {
            $query += ["token" => $this->token];

            $filtered_query = array_filter($query, function ($elem) {
                return !empty($elem) and $elem != "null";
            });

            $this->logger->debug("[GET] /bot/v1" . $path);

            $result = $this->session->get("/bot/v1" . $path, [
                "query" => $filtered_query
            ])->getBody();

            $this->logger->debug($result);

            return json_decode($result, true);
        }

        /**
         * @param string $path Path запроса к API
         * @param array $query Query параметры запроса
         * @param string $file_path Путь к отправляемому файлу
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        private function post(string $path, array $query, string $file_path): array
        {
            $query += ["token" => $this->token];

            $filtered_query = array_filter($query, function ($elem) {
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

        /**
         * @param string $chatId ID чата
         * @param string $text Текст сообщения
         * @param string|int|null $replyMsgId ID сообщения, на которое бот отвечает
         * @param string|null $forwardChatId ID чата пересылаемого сообщения
         * @param array|null $forwardMsgId ID пересылаемого сообщения
         * @param array|Keyboard|null $inlineKeyboardMarkup Кнопки к сообщению бота
         * @param object|null $format Форматирование текста (Только для ручного форматирования текста)
         * @param string|null $parseMode Режим форматирования текста сообщения
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function sendText(
            string              $chatId,
            string              $text,
            string|int|null     $replyMsgId = NULL,
            string|null         $forwardChatId = NULL,
            array|null          $forwardMsgId = NULL,
            array|Keyboard|null $inlineKeyboardMarkup = NULL,
            object|null         $format = NULL,
            string|null         $parseMode = NULL,
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

        /**
         * @param string $chatId ID чата
         * @param string|null $fileId ID отправляемого файла (Только для уже загруженных файлов)
         * @param string|null $filePath Путь к загружаемому файлу
         * @param string|null $caption Описание к отправляемому файлу
         * @param string|int|null $replyMsgId ID сообщения, на которое бот отвечает
         * @param string|null $forwardChatId ID чата пересылаемого сообщения
         * @param array|null $forwardMsgId ID пересылаемого сообщения
         * @param array|Keyboard|null $inlineKeyboardMarkup Кнопки к сообщению бота
         * @param object|null $format Форматирование текста (Только для ручного форматирования текста)
         * @param string|null $parseMode Режим форматирования текста сообщения
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function sendFile(
            string              $chatId,
            string|null         $fileId = NULL,
            string|null         $filePath = NULL,
            string|null         $caption = NULL,
            string|int|null     $replyMsgId = NULL,
            string|null         $forwardChatId = NULL,
            array|null          $forwardMsgId = NULL,
            array|Keyboard|null $inlineKeyboardMarkup = NULL,
            object|null         $format = NULL,
            string|null         $parseMode = NULL,
        ): array
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

        /**
         * @param string $chatId ID чата
         * @param string|null $fileId ID отправляемого файла (Только для уже загруженных файлов)
         * @param string|null $filePath Путь к загружаемому файлу
         * @param string|null $caption Описание к отправляемому файлу
         * @param string|int|null $replyMsgId ID сообщения, на которое бот отвечает
         * @param string|null $forwardChatId ID чата пересылаемого сообщения
         * @param array|null $forwardMsgId ID пересылаемого сообщения
         * @param array|Keyboard|null $inlineKeyboardMarkup Кнопки к сообщению бота
         * @param object|null $format Форматирование текста (Только для ручного форматирования текста)
         * @param string|null $parseMode Режим форматирования текста сообщения
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function sendVoice(
            string              $chatId,
            string|null         $fileId = NULL,
            string|null         $filePath = NULL,
            string|null         $caption = NULL,
            string|int|null     $replyMsgId = NULL,
            string|null         $forwardChatId = NULL,
            array|null          $forwardMsgId = NULL,
            array|Keyboard|null $inlineKeyboardMarkup = NULL,
            object|null         $format = NULL,
            string|null         $parseMode = NULL,
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

        /**
         * @param string $chatId ID чата
         * @param string|int $msgId ID редактируемого сообщения
         * @param string $text Новый текст сообщения
         * @param string|int|null $replyMsgId ID сообщения, на которое бот отвечает
         * @param string|null $forwardChatId ID чата пересылаемого сообщения
         * @param array|null $forwardMsgId ID пересылаемого сообщения
         * @param array|Keyboard|null $inlineKeyboardMarkup Кнопки к сообщению бота
         * @param object|null $format Форматирование текста (Только для ручного форматирования текста)
         * @param string|null $parseMode Режим форматирования текста сообщения
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function editText(
            string              $chatId,
            string|int          $msgId,
            string              $text,
            string|int|null     $replyMsgId = NULL,
            string|null         $forwardChatId = NULL,
            array|null          $forwardMsgId = NULL,
            array|Keyboard|null $inlineKeyboardMarkup = NULL,
            object|null         $format = NULL,
            string|null         $parseMode = NULL,
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

        /**
         * @param string $chatId ID чата
         * @param string|int $msgId ID удаляемого сообщения
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function deleteMessage(
            string     $chatId,
            string|int $msgId
        ): array
        {
            return $this->get("/messages/deleteMessages", [
                "chatId" => $chatId,
                "msgId" => $msgId
            ]);
        }

        /**
         * @param string $queryId Идентификатор callback query полученного ботом
         * @param string|null $text Текст нотификации, который будет отображен пользователю. В случае, если текст не задан – ничего не будет отображено.
         * @param bool $showAlert Если выставить значение в true, вместо нотификации будет показан alert
         * @param string|null $url URL, который будет открыт клиентским приложением
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function answerCallbackQuery(
            string      $queryId,
            string|null $text = NULL,
            bool        $showAlert = false,
            string|null $url = NULL,
        ): array
        {
            return $this->get("/messages/answerCallbackQuery", [
                "queryId" => $queryId,
                "text" => $text,
                "showAlert" => $showAlert,
                "url" => $url
            ]);
        }

        /**
         * @param string $name Название чата.
         * @param string|null $about Описание чата.
         * @param string|null $rules Правила чата.
         * @param array $members Список пользователей
         * @param bool $public Публичность чата
         * @param string $defaultRole Роль по умолчанию ('member' для групп, 'readonly' для каналов)
         * @param bool $joinModeration Требуется ли подтверждение вступления.
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function createChat(
            string      $name,
            string|null $about = NULL,
            string|null $rules = NULL,
            array       $members = [],
            bool        $public = false,
            string      $defaultRole = 'member',
            bool        $joinModeration = true
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

        /**
         * @param string $chatId ID чата
         * @param array $members Список пользователей
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatMembersAdd(
            string $chatId,
            array  $members,
        ): array
        {
            return $this->get("/chats/members/add", [
                "chatId" => $chatId,
                "members" => json_encode($members)
            ]);
        }

        /**
         * @param string $chatId ID чата
         * @param array $members Список пользователей
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatMembersDelete(
            string $chatId,
            array  $members,
        ): array
        {
            return $this->get("/chats/members/delete", [
                "chatId" => $chatId,
                "members" => json_encode($members)
            ]);
        }

        /**
         * @param string $chatId ID чата
         * @param string $action Текущие действия в чате. Отправьте пустое значение, если все действия завершены.
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatsSendAction(
            string $chatId,
            string $action
        ): array
        {
            return $this->get("/chats/sendActions", [
                "chatId" => $chatId,
                "actions" => $action
            ]);
        }

        /**
         * @param string $chatId ID чата
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatGetInfo(
            string $chatId,
        ): array
        {
            return $this->get("/chats/getInfo", [
                "chatId" => $chatId
            ]);
        }

        /**
         * @param string $chatId ID чата
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatGetAdmins(
            string $chatId,
        ): array
        {
            return $this->get("/chats/getAdmins", [
                "chatId" => $chatId
            ]);
        }

        /**
         * @param string $chatId ID чата
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatGetMembers(
            string $chatId,
        ): array
        {
            return $this->get("/chats/getMembers", [
                "chatId" => $chatId
            ]);
        }

        /**
         * @param string $chatId ID чата
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatGetBlockedUsers(
            string $chatId,
        ): array
        {
            return $this->get("/chats/getBlockedUsers", [
                "chatId" => $chatId
            ]);
        }

        /**
         * @param string $chatId ID чата
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatGetPendingUsers(
            string $chatId,
        ): array
        {
            return $this->get("/chats/getPendingUsers", [
                "chatId" => $chatId
            ]);
        }

        /**
         * @param string $chatId ID чата
         * @param string $userId Уникальный ник или id пользователя.
         * @param bool $delLastMessages Удаление последних сообщений заданного пользователя в чате.
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatsBlockUser(
            string $chatId,
            string $userId,
            bool   $delLastMessages
        ): array
        {
            return $this->get("/chats/blockUser", [
                "chatId" => $chatId,
                "userId" => $userId,
                "delLastMessages" => $delLastMessages
            ]);
        }

        /**
         * @param string $chatId ID чата
         * @param string $userId Уникальный ник или id пользователя.
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatsUnblockUser(
            string $chatId,
            string $userId,
        ): array
        {
            return $this->get("/chats/unblockUser", [
                "chatId" => $chatId,
                "userId" => $userId
            ]);
        }

        /**
         * @param string $chatId ID чата
         * @param bool $approve Положительное или отрицательное решение.
         * @param string|null $userId Ник или id пользователя, ожидающего вступления в чат. Не может быть передано с параметром everyone.
         * @param bool|null $everyone Решение обо всех пользователях, ожидающих вступления в чат. Не может быть передано с параметром userId.
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatsResolvePending(
            string      $chatId,
            bool        $approve,
            string|null $userId = NULL,
            bool|null   $everyone = NULL
        ): array
        {
            if ($userId != NULL) {
                return $this->get("/chats/resolvePending", [
                    "chatId" => $chatId,
                    "approve" => $approve,
                    "userId" => $userId
                ]);
            } elseif ($everyone != NULL) {
                return $this->get("/chats/resolvePending", [
                    "chatId" => $chatId,
                    "approve" => $approve,
                    "everyone" => $everyone
                ]);
            } else {
                throw new Exception("userId or everyone must be provided");
            }
        }

        /**
         * @param string $chatId ID чата
         * @param string $title Название чата.
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatsSetTitle(
            string $chatId,
            string $title,
        ): array
        {
            return $this->get("/chats/setTitle", [
                "chatId" => $chatId,
                "title" => $title
            ]);
        }

        /**
         * @param string $chatId ID чата
         * @param string $image_path Путь к изображению
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatsAvatarSet(
            string $chatId,
            string $image_path,
        ): array
        {
            return $this->post("/chats/avatar/set", [
                "chatId" => $chatId
            ],
                $image_path);
        }

        /**
         * @param string $chatId ID чата
         * @param string $about Описание чата.
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatsSetAbout(
            string $chatId,
            string $about,
        ): array
        {
            return $this->get("/chats/setTitle", [
                "chatId" => $chatId,
                "about" => $about
            ]);
        }

        /**
         * @param string $chatId ID чата
         * @param string $rules Правила чата
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function chatsSetRules(
            string $chatId,
            string $rules,
        ): array
        {
            return $this->get("/chats/setTitle", [
                "chatId" => $chatId,
                "rules" => $rules
            ]);
        }

        /**
         * @param string $chatId ID чата
         * @param string|int $msgId ID сообщения
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function pinMessage(
            string     $chatId,
            string|int $msgId
        ): array
        {
            return $this->get("/chats/pinMessage", [
                "chatId" => $chatId,
                "msgId" => $msgId
            ]);
        }

        /**
         * @param string $chatId ID чата
         * @param string|int $msgId ID сообщения
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function unpinMessage(
            string     $chatId,
            string|int $msgId
        ): array
        {
            return $this->get("/chats/unpinMessage", [
                "chatId" => $chatId,
                "msgId" => $msgId
            ]);
        }

        /**
         * @param string $fileId ID файла
         * @return array Ответ API на запрос
         * @throws GuzzleException
         */
        public function filesGetInfo(
            string $fileId
        ): array
        {
            return $this->get("/files/getInfo", [
                "fileId" => $fileId
            ]);
        }

        /**
         * @return array
         * @throws GuzzleException
         */
        public function eventsGet(): array
        {
            try {
                $events = $this->get("/events/get", [
                    "lastEventId" => $this->lastEventId,
                    "pollTime" => $this->pollTime
                ]);

                if ($events["events"]) {
                    $lastEvent = end($events["events"]);
                    reset($events["events"]);

                    $this->lastEventId = $lastEvent["eventId"];
                }
                return $events["events"];
            } catch (TypeError|RequestException $e) {
                $this->logger->error($e);
                return [];
            }
        }

        /**
         * @param $event_type
         * @param callable $lambda Функция-обработчик события
         * @param string|null $cmd Команда
         * @return void
         */
        private function on(
            $event_type,
            callable $lambda,
            ?string $cmd = NULL,
        ): void
        {
            if ($cmd != NULL and $event_type == EventsType::NEW_MESSAGE) {
                $this->commands[] = $cmd;
                $this->eventCheckers[$cmd][] = $lambda;
            } else {
                $this->eventCheckers[$event_type->value][] = $lambda;
            }
        }

        /**
         * @param callable $lambda Функция-обработчик события
         * @return void
         */
        public function onMessage(
            callable $lambda,
        ): void
        {
            $this->on(
                EventsType::NEW_MESSAGE,
                $lambda
            );
        }

        /**
         * @param callable $lambda Функция-обработчик события
         * @param string $cmd Команда в сообщении
         * @return void
         */
        public function command(
            string   $cmd,
            callable $lambda,
        ): void
        {
            $this->on(
                EventsType::NEW_MESSAGE,
                $lambda,
                $cmd
            );
        }

        /**
         * @param callable $lambda Функция-обработчик события
         * @return void
         */
        public function onEditedMessage(
            callable $lambda
        ): void
        {
            $this->on(
                EventsType::EDITED_MESSAGE,
                $lambda
            );
        }

        /**
         * @param callable $lambda Функция-обработчик события
         * @return void
         */
        public function onDeletedMessage(
            callable $lambda,
        ): void
        {
            $this->on(
                EventsType::DELETED_MESSAGE,
                $lambda
            );
        }

        /**
         * @param callable $lambda Функция-обработчик события
         * @return void
         */
        public function onPinnedMessage(
            callable $lambda,
        ): void
        {
            $this->on(
                EventsType::PINNED_MESSAGE,
                $lambda
            );
        }

        /**
         * @param callable $lambda Функция-обработчик события
         * @return void
         */
        public function onUnpinnedMessage(
            callable $lambda
        ): void
        {
            $this->on(
                EventsType::UNPINNED_MESSAGE,
                $lambda
            );
        }

        /**
         * @param callable $lambda Функция-обработчик события
         * @return void
         */
        public function onNewChatMember(
            callable $lambda,
        ): void
        {
            $this->on(
                EventsType::NEW_CHAT_MEMBER,
                $lambda
            );
        }

        /**
         * @param callable $lambda Функция-обработчик события
         * @return void
         */
        public function onLeftChatMember(
            callable $lambda
        ): void
        {
            $this->on(
                EventsType::LEFT_CHAT_MEMBER,
                $lambda
            );
        }

        /**
         * @param callable $lambda Функция-обработчик события
         * @return void
         */
        public function onCallbackQuery(
            callable $lambda
        ): void
        {
            $this->on(
                EventsType::CALLBACK_QUERY,
                $lambda
            );
        }

        /**
         * @return void
         */
        public function pollEvents(): void
        {
            while (true):

                foreach ($this->eventsGet() as &$event) {

                    if ($event["type"] == EventsType::NEW_MESSAGE->value) {
                        foreach ($this->commands as $command) {

                            if (str_starts_with($event["payload"]["text"], $command)) {
                                foreach ($this->eventCheckers[$command] as $eventChecker) {
                                    $eventChecker($this, $event);
                                }

                                continue 3;
                            }
                        }
                    }

                    if (array_key_exists($event["type"], $this->eventCheckers)) {

                        foreach ($this->eventCheckers[$event["type"]] as $eventChecker) {
                            $eventChecker($this, $event);
                        }
                    }

                }

            endwhile;
        }
    }
}