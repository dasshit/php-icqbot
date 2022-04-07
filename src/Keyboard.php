<?php

namespace Dasshit\IcqBot {

    use JsonSerializable;


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