<?php
class SubscriptionObject implements JsonSerializable
    {
        private string $optionTitle;
        private string $description;
        private float $price;
        private ?float $discount;

        public function __construct(string $optionTitle, string $description, float $price, ?float $discount) {
            $this->optionTitle = $optionTitle;
            $this->description = $description;
            $this->price = $price;
            $this->discount = $discount;
        }

        public function getPrice(): float
        {
            return $this->price;
        }

        public function jsonSerialize()
        {
            return get_object_vars($this);
        }
    }
?>