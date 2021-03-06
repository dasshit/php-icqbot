# ICQ/VK Teams bot API wrapper

<img src="https://icq.com/botapi/res/logo_icq_new.png" width="40%"><img src="https://myteam.mail.ru/botapi/res/logo_myteam.png" width="40%">

## Install (Composer)

```
composer require dasshit/icq_bot
```

## Usage example

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;

use Dasshit\IcqBot as ICQ;


$bot = new ICQ\Bot(
    'TOKEN',
    "https://api.icq.net/bot/v1", 
    log_level: Logger::DEBUG,
    log_path: 'bot_' . date("h-i-s") . '.log'
);


$keyboard = new ICQ\Keyboard();

$keyboard->addButton(
    new ICQ\Button(text: "Test 1", url: "https://yandex.ru/")
);

$keyboard->addRow(
    [
        new ICQ\Button(text: "Test 2", callbackData: "test"),
        new ICQ\Button(text: "Test 3", callbackData: "test"),
        new ICQ\Button(text: "Test 4", callbackData: "test")
    ]
);

$keyboard->addButton(
    new ICQ\Button(text: "Test 5", url: "https://yandex.ru/")
);


$bot->command("/start", function (ICQ\Bot $bot, $event) {

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