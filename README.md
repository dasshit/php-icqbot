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

    foreach ($bot->eventsGet() as &$event) {

        if ($event["type"] == EventsType::NEW_MESSAGE->value) {

            $bot->sendText($event["payload"]["chat"]["chatId"], $event["payload"]["text"]);

        }
    }

    endwhile;
```