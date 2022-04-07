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
    log_level: Logger::DEBUG
);


while (true):

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

    foreach ($bot->eventsGet() as &$event) {

        $chatId = $event["payload"]["chat"]["chatId"];

        match ($event["type"]) {
            EventsType::NEW_MESSAGE->value => $bot->sendText(
                $chatId, $event["payload"]["text"],
                inlineKeyboardMarkup: $keyboard
            ),

            EventsType::EDITED_MESSAGE->value => $bot->sendText(
                $chatId, "@[$chatId]",
                inlineKeyboardMarkup: $keyboard
            ),

        };

    }

    endwhile;
```