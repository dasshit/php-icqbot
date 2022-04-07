<?php

namespace Dasshit\IcqBot {

    use JsonSerializable;

    /**
     * class Keyboard
     * @package Dasshit\IcqBot
     */
    class Keyboard implements JsonSerializable
    {
        public array $rows;

        public function __construct()
        {
            $this->rows = [];
        }

        /**
         * @param array $row Массив кнопок в клавиатуре
         * @return void
         */
        public function addRow(array $row): void
        {
            $this->rows[] = $row;
        }

        /**
         * @param Button $button Кнопка для клавиатуры
         * @return void
         */
        public function addButton(Button $button): void
        {
            $this->rows[] = [$button];
        }

        /**
         * @return array
         */
        public function jsonSerialize(): array
        {
            return $this->rows;
        }
    }
}