<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

use Doctrine\Common\Collections\ArrayCollection;

abstract class AbstractPlayer
{
    /** @var int */
    public $cardsToDraw;

    /** @var ArrayCollection|AbstractHandCard[] */
    protected $hand;

    /** @var Deck */
    private $deck;

    public function __construct(Deck $deck, int $cardsToDrawAtStart, int $cardsToDraw)
    {
        $this->cardsToDraw = $cardsToDraw;
        $this->deck = $deck;
        $this->hand = new ArrayCollection();

        $this->draw($cardsToDrawAtStart);
    }

    public function draw(int $number = 1)
    {
        for ($i = 0; $i < $number; $i++) {
            $this->drawOne();
        }
    }

    public function discard(int $number)
    {
        for ($i = 0; $i < $number; $i++) {
            $this->hand->removeElement($this->hand->last());
        }
    }

    public function playCards(array $cards): void
    {
        foreach ($cards as $card) {
            $this->playCard($card);
        }
    }

    public static function roll(int $bonus = 0): int
    {
        return \mt_rand(1, 6) + $bonus;
    }

    public function handCount(): int
    {
        return $this->hand->count();
    }

    protected function drawOne()
    {
        $this->hand->add($this->deck->draw());
    }

    protected function playCard(AbstractHandCard $card): void
    {
        $this->hand->removeElement($card);
    }
}
