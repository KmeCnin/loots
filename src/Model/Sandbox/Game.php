<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

class Game
{
    public $gm;

    /** @var Adventurer[] */
    public $adventurers;

    /** @var AbstractPlace[] */
    public $places;

    public $ended;

    public $adventurersWin;

    public $deckPlaces;

    public function __construct(GM $gm, array $adventurers)
    {
        $this->gm = $gm;
        $this->adventurers = $adventurers;
        $this->places = [];
        $this->ended = false;
        $this->adventurersWin = null;

        $this->deckPlaces = new Deck();
        for ($i = 0; $i < 2; $i++) {
            $this->deckPlaces->add(new Boss());
        }
        for ($i = 0; $i < 29; $i++) {
            $this->deckPlaces->add(new Region());
        }
        for ($i = 0; $i < 10; $i++) {
            $this->deckPlaces->add(new Layover());
        }
    }

    public function play(): void
    {
        while (!$this->ended) {
            // Phase 1.
            $this->trip();
            // Phase 2.
            $this->explore();
            // Phase 3.
            $this->settle();
            // Phase 4.
            $this->loot();
        }
    }

    /**
     * @return Adventurer[]
     */
    public function aliveAdventurers(): array
    {
        return array_filter(
            $this->adventurers,
            function (Adventurer $adventurer) {
                return $adventurer->alive;
            }
        );
    }

    public function currentPlace(): AbstractPlace
    {
        return \end($this->places);
    }

    private function trip(): void
    {
        $newPlace = $this->deckPlaces->draw();

//        if ($newPlace instanceof Layover) {
//            $nope = clone $newPlace;
//            $newPlace = $this->deckPlaces->draw();
//            $this->deckPlaces->add($nope);
//        }

        if ($newPlace instanceof Boss && 3 > \count($this->places)) {
            $toSoon = clone $newPlace;
            $newPlace = $this->deckPlaces->draw();
            $this->deckPlaces->add($toSoon);
        }

        $this->places[] = $newPlace;
    }

    private function explore(): void
    {
        foreach ($this->adventurers as $adventurer) {
            $adventurer->equip();
        }
        $this->currentPlace()->explore($this);
    }

    private function settle(): void
    {
        $this->currentPlace()->settle($this);
        // Lose.
        if (0 === \count($this->aliveAdventurers())) {
            $this->ended = true;
            $this->adventurersWin = false;
            return;
        }
        // Win.
        if ($this->currentPlace() instanceof Boss) {
            $this->ended = true;
            $this->adventurersWin = true;
            return;
        }
    }

    private function loot(): void
    {
        if ($this->currentPlace() instanceof Region) {
            foreach ($this->aliveAdventurers() as $adventurer) {
                $adventurer->draw($adventurer->cardsToDraw);
            }
        }
        $this->gm->levelUp();
    }
}
