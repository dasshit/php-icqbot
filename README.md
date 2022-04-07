## Usage example

```php
<?php

use Monolog\Logger;

require_once('vendor/autoload.php');

require './src/bot.php';
require './src/events.php';


$bot = new Bot(
    'TOKEN',
    "https://api.icq.net/bot/v1",
    log_level: Logger::DEBUG,
    log_path: 'bot_' . date("Y-M-d") . '.log'
);


$keyboard = new Keyboard();

$keyboard->addButton(
    new Button(text: "Test 1", url: "https://yandex.ru/")
);

$keyboard->addRow(
    [
        new Button(text: "Test 2", callbackData: "test"),
        new Button(text: "Test 3", callbackData: "test"),
        new Button(text: "Test 4", callbackData: "test")
    ]
);

$keyboard->addButton(
    new Button(text: "Test 5", url: "https://yandex.ru/")
);


$bot->command("/start", function (Bot $bot, $event) {

    $bot->logger->debug($event["type"]);

    $chatId = $event["payload"]["chat"]["chatId"];

    $bot->sendText(
        $chatId,
        "Hi, @[$chatId]"
    );

});


$bot->onMessage(function ($bot, $event) {

    $bot->logger->debug($event["type"]);

    $chatId = $event["payload"]["chat"]["chatId"];

    $bot->sendText(
        $chatId,
        "Message, @[$chatId]"
    );

});


$bot->onEditedMessage(function ($bot, $event) {

    $bot->logger->debug($event["type"]);

    $chatId = $event["payload"]["chat"]["chatId"];

    $bot->sendText(
        $chatId,
        "Edit, @[$chatId]"
    );
});

$bot->pollEvents();
```