<?php

namespace Dasshit\ICQBot {

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


    class Keyboard implements JsonSerializable
    {
        public array $rows;

        public function __construct()
        {
            $this->rows = [];
        }

        public function addRow(array $row): void
        {
            $this->rows[] = $row;
        }

        public function addButton(Button $button): void
        {
            $this->rows[] = [$button];
        }

        public function jsonSerialize(): array
        {
            return $this->rows;
        }
    }
}