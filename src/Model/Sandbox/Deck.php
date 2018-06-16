<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class Deck
{
    /** @var AbstractCard[] */
    private $cards;

    public function __construct(array $cards = [])
    {
        $this->cards = $cards;
    }

    public function add(AbstractCard $card)
    {
        $this->cards[] = $card;
    }

    public function draw(): AbstractCard
    {
        $index = mt_rand(0, \count($this->cards)-1);
        $card = $this->cards[$index];
        unset($this->cards[$index]);
        $this->cards = array_values($this->cards);

        return $card;
    }
}
