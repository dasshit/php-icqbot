<?php

namespace Dasshit\IcqBot;

use JsonSerializable;

class Button implements JsonSerializable
{
    private string $text;
    private ?string $url;
    private ?string $callbackData;
    private string $style;

    public function __construct(
        string  $text,
        ?string $url = NULL,
        ?string $callbackData = NULL,
        string  $style = "primary"
    )
    {
        $this->text = $text;
        $this->url = $url;
        $this->callbackData = $callbackData;
        $this->style = $style;
    }

    public function jsonSerialize(): array
    {

        $jsonArray = array(
            "text" => $this->text,
            "url" => $this->url,
            "callbackData" => $this->callbackData,
            "style" => $this->style
        );

        return array_filter($jsonArray, function ($elem) {
            return !empty($elem) and $elem != "null";
        });
    }
}