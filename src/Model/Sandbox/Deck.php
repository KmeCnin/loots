<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

use Doctrine\Common\Collections\ArrayCollection;

class Deck
{
    /** @var ArrayCollection|AbstractCard[] */
    private $cards;

    public function __construct(array $cards = [])
    {
        $this->cards = new ArrayCollection($cards);
    }

    public function add(AbstractCard $card)
    {
        $this->cards->add($card);
    }

    public function draw(): AbstractCard
    {
        if (!$this->hasCards()) {
            throw new \Exception('No more card in deck');
        }

        $index = mt_rand(0, $this->cardsCount() - 1);
        $cards = \array_values($this->cards->toArray());
        $card = $cards[$index];
        $this->cards->removeElement($card);

        return $card;
    }

    public function cardsCount(): int
    {
        return $this->cards->count();
    }

    public function hasCards(): bool
    {
        return !$this->cards->isEmpty();
    }

    public function __clone()
    {
        $cards = [];
        foreach ($this->cards->toArray() as $card) {
            $cards[] = clone $card;
        }
        $this->cards = new ArrayCollection($cards);
    }
}
