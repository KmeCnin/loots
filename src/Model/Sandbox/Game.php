<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

use App\Controller\SandboxController;

class Game
{
    public static $debug = false;

    public static $canonicalDeckPlaces;

    public static $canonicalDeckTests;

    public static $canonicalDeckLoots;

    public $deckPlaces;

    public $gm;

    /** @var Adventurer[] */
    public $adventurers;

    /** @var AbstractPlace[] */
    public $places;

    public $ended;

    public $adventurersWin;

    public $adventurersTotalHand;

    public $gmTotalHand;

    public function __construct(Parameters $parameters)
    {
        self::initDeckPlaces();
        self::initDeckTest();
        self::initDeckLoots();

        $this->deckPlaces = $this->newDeckPlaces();

        $this->gm = new GM(
            $this->newDeckTests(),
            $parameters->gmDrawAtStart,
            $parameters->gmCardsToDraw // + \count($adventurers) - 1
        );

        $this->places = [];
        $this->ended = false;
        $this->adventurersWin = null;
        $this->adventurersTotalHand = 0;
        $this->gmTotalHand = 0;

        for ($j = 0; $j < $parameters->numberOfAdventurers; $j++) {
            $race = SandboxController::races()[$parameters->race];
            $this->adventurers[] = new Adventurer(
                $race[0],
                $race[1],
                $race[2],
                $this->newDeckLoots(),
                $parameters->adventurersDrawAtStart,
                $parameters->adventurersCardsToDraw
            );
        }
    }

    public static function initDeckPlaces(): void
    {
        if (null !== self::$canonicalDeckPlaces) {
            return;
        }

        self::$canonicalDeckPlaces = new Deck();
        for ($i = 0; $i < 1; $i++) {
            self::$canonicalDeckPlaces->add(new Boss());
        }
        for ($i = 0; $i < 29; $i++) {
            self::$canonicalDeckPlaces->add(new Region());
        }
        for ($i = 0; $i < 10; $i++) {
            self::$canonicalDeckPlaces->add(new Layover());
        }
    }

    public static function initDeckTest(): void
    {
        if (null !== self::$canonicalDeckTests) {
            return;
        }

        $bonus = 2;

        self::$canonicalDeckTests = new Deck();
        for ($i = 0; $i < 20; $i++) {
            self::$canonicalDeckTests->add(new Test(1, 2, 2, [Skill::FIGHT => $bonus]));
            self::$canonicalDeckTests->add(new Test(2, 1, 2, [Skill::FIGHT => $bonus]));
            self::$canonicalDeckTests->add(new Test(2, 2, 1, [Skill::FIGHT => $bonus]));
            self::$canonicalDeckTests->add(new Test(1, 2, 2, [Skill::TRICK => $bonus]));
            self::$canonicalDeckTests->add(new Test(2, 1, 2, [Skill::TRICK => $bonus]));
            self::$canonicalDeckTests->add(new Test(2, 2, 1, [Skill::TRICK => $bonus]));
            self::$canonicalDeckTests->add(new Test(1, 2, 2, [Skill::MAGIC => $bonus]));
            self::$canonicalDeckTests->add(new Test(2, 1, 2, [Skill::MAGIC => $bonus]));
            self::$canonicalDeckTests->add(new Test(2, 2, 1, [Skill::MAGIC => $bonus]));
        }
    }

    public static function initDeckLoots(): void
    {
        if (null !== self::$canonicalDeckLoots) {
            return;
        }

        $bonus = 2;

        self::$canonicalDeckLoots = new Deck();
        for ($i = 0; $i < 10; $i++) {
            self::$canonicalDeckLoots->add(new Loot(2, 0, 0, [Skill::FIGHT => $bonus]));
            self::$canonicalDeckLoots->add(new Loot(0, 2, 0, [Skill::FIGHT => $bonus]));
            self::$canonicalDeckLoots->add(new Loot(0, 0, 2, [Skill::FIGHT => $bonus]));
            self::$canonicalDeckLoots->add(new Loot(2, 0, 0, [Skill::TRICK => $bonus]));
            self::$canonicalDeckLoots->add(new Loot(0, 2, 0, [Skill::TRICK => $bonus]));
            self::$canonicalDeckLoots->add(new Loot(0, 0, 2, [Skill::TRICK => $bonus]));
            self::$canonicalDeckLoots->add(new Loot(2, 0, 0, [Skill::MAGIC => $bonus]));
            self::$canonicalDeckLoots->add(new Loot(0, 2, 0, [Skill::MAGIC => $bonus]));
            self::$canonicalDeckLoots->add(new Loot(0, 0, 2, [Skill::MAGIC => $bonus]));
        }
    }

    public static function scoreIsEnough(int $score): bool
    {
        return $score > 0;
    }

    public function play(): void
    {
        Game::log('<br>--------------------');
        Game::log('Starting new game.');
        Game::log('--------------------');

        while (!$this->ended) {

            Game::log(sprintf(
                '<br> --- Starting turn number %d.',
                \count($this->places) + 1
            ));

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

    /**
     * @return Adventurer[]
     */
    public function deadAdventurers(): array
    {
        return array_filter(
            $this->adventurers,
            function (Adventurer $adventurer) {
                return !$adventurer->alive;
            }
        );
    }

    public function anAdventurerNeedsHealthCare(): bool
    {
        foreach ($this->adventurers as $adventurer) {
            if ($adventurer->wounds > 0 || !$adventurer->alive) {
                return true;
            }
        }

        return false;
    }

    public function currentPlace(): AbstractPlace
    {
        return \end($this->places);
    }

    public static function log(string $message)
    {
        if (self::$debug) {
            echo $message.'<br />';
        }
    }

    private function newDeckPlaces(): Deck
    {
        return clone self::$canonicalDeckPlaces;
    }

    private function newDeckTests(): Deck
    {
        return clone self::$canonicalDeckTests;
    }

    private function newDeckLoots(): Deck
    {
        return clone self::$canonicalDeckLoots;
    }

    private function trip(): void
    {
        $newPlace = $this->deckPlaces->draw();

        if ($newPlace instanceof Layover && !$this->anAdventurerNeedsHealthCare()) {
            // Avoid Layover.
            $nope = clone $newPlace;
            $newPlace = $this->deckPlaces->draw();
            $this->deckPlaces->add($nope);
        }

        if ($newPlace instanceof Boss && $this->gm->slots() < 3) {
            // Avoid Boss.
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
        foreach ($this->adventurers as $adventurer) {
            $this->adventurersTotalHand += \count($adventurer->hand);
        }
        $this->gmTotalHand += \count($this->gm->hand);

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
