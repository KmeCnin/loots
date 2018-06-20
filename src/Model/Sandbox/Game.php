<?php

declare(strict_types=1);

namespace App\Model\Sandbox;

use App\Controller\SandboxController;
use Doctrine\Common\Collections\ArrayCollection;

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

    private $players;

    public function __construct(Parameters $parameters)
    {
        self::initDeckPlaces();
        self::initDeckTest();
        self::initDeckLoots();

        $this->deckPlaces = $this->newDeckPlaces();

        $this->gm = new GM(
            $this->newDeckTests(),
            $parameters->gmDrawAtStart,
            $parameters->gmCardsToDraw + $parameters->numberOfAdventurers - 1
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

        $this->players = new ArrayCollection($this->adventurers);
        $this->players->add($this->gm);
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
        $a = 1;
        $b = 1;
        $c = 2;

        self::$canonicalDeckTests = new Deck();
        for ($i = 0; $i < 30; $i++) {
            self::$canonicalDeckTests->add(new Test($a, $b, $c, [
                Skill::FIGHT => $bonus,
            ]));
            self::$canonicalDeckTests->add(new Test($c, $a, $b, [
                Skill::FIGHT => $bonus
            ]));
            self::$canonicalDeckTests->add(new Test($b, $c, $a, [
                Skill::FIGHT => $bonus
            ]));
            self::$canonicalDeckTests->add(new Test($a, $b, $c, [
                Skill::TRICK => $bonus
            ]));
            self::$canonicalDeckTests->add(new Test($c, $a, $b, [
                Skill::TRICK => $bonus
            ]));
            self::$canonicalDeckTests->add(new Test($b, $c, $a, [
                Skill::TRICK => $bonus
            ]));
            self::$canonicalDeckTests->add(new Test($a, $b, $c, [
                Skill::MAGIC => $bonus
            ]));
            self::$canonicalDeckTests->add(new Test($c, $a, $b, [
                Skill::MAGIC => $bonus
            ]));
            self::$canonicalDeckTests->add(new Test($b, $c, $a, [
                Skill::MAGIC => $bonus
            ]));
        }
    }

    public static function initDeckLoots(): void
    {
        if (null !== self::$canonicalDeckLoots) {
            return;
        }

        $bonus = 2;
        $a = 2;
        $b = 0;
        $c = 0;

        self::$canonicalDeckLoots = new Deck();
        for ($i = 0; $i < 10; $i++) {
            self::$canonicalDeckLoots->add(new Loot($a, $b, $c, [
                Skill::FIGHT => $bonus,
            ]));
            self::$canonicalDeckLoots->add(new Loot($c, $a, $b, [
                Skill::FIGHT => $bonus
            ]));
            self::$canonicalDeckLoots->add(new Loot($b, $c, $a, [
                Skill::FIGHT => $bonus
            ]));
            self::$canonicalDeckLoots->add(new Loot($a, $b, $c, [
                Skill::TRICK => $bonus
            ]));
            self::$canonicalDeckLoots->add(new Loot($a, $b, $c, [
                Skill::TRICK => $bonus
            ]));
            self::$canonicalDeckLoots->add(new Loot($b, $c, $a, [
                Skill::TRICK => $bonus
            ]));
            self::$canonicalDeckLoots->add(new Loot($a, $b, $c, [
                Skill::MAGIC => $bonus
            ]));
            self::$canonicalDeckLoots->add(new Loot($c, $a, $b, [
                Skill::MAGIC => $bonus
            ]));
            self::$canonicalDeckLoots->add(new Loot($b, $c, $a, [
                Skill::MAGIC => $bonus
            ]));
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
            $this->prepare();
            // Phase 3.
            $this->explore();
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
        $placesToDraw = 2;
        /** @var AbstractPlace[] $challengers */
        $challengers = [];

        for ($i = 0; $i < $placesToDraw; $i++) {
            if ($this->deckPlaces->hasCards()) {
                $challengers[] = $this->deckPlaces->draw();
            }
        }

        // Go to Layover if needed.
        if ($this->anAdventurerNeedsHealthCare()) {
            foreach ($challengers as $place) {
                if ($place instanceof Layover) {
                    $placeToMove = $place;
                    goto move;
                }
            }
        }

        // Avoid Boss if GM not level max.
        foreach ($challengers as $place) {
            if ($place->slotsForTest($this->gm) < 3) {
                $placeToMove = $place;
                goto move;
            }
        }

        // Go to Boss if GM level max.
        foreach ($challengers as $place) {
            if ($place instanceof Boss) {
                $placeToMove = $place;
                goto move;
            }
        }

        // Go to not Layover place.
        foreach ($challengers as $place) {
            if (!$place instanceof Layover) {
                $placeToMove = $place;
                goto move;
            }
        }

        // Get a random place.
        $placeToMove = $challengers[mt_rand(0, \count($challengers)  - 1)];

        move:
        foreach ($challengers as $place) {
            if ($placeToMove === $place) {
                self::log(sprintf('Moving to place %s', get_class($place)));

                $this->places[] = $place;
            } else {
                $this->deckPlaces->add($place);
            }
        }
    }

    private function prepare(): void
    {
        // Adventurers equip themselves.
        foreach ($this->aliveAdventurers() as $adventurer) {
            $adventurer->equip();
        }

        // GM add the tests to the current place.
        $this->gm->preparePlaceForAdventurers(
            $this->currentPlace(),
            $this->aliveAdventurers()
        );
    }

    private function explore(): void
    {
        $this->snapshotHandsCounts();

        // Adventurers try to explore the current place one by one.
        $this->currentPlace()->settle($this->gm, $this->aliveAdventurers());

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
        $this->currentPlace()->reward($this->gm, $this->aliveAdventurers());
    }

    private function snapshotHandsCounts()
    {
        foreach ($this->adventurers as $adventurer) {
            $this->adventurersTotalHand += $adventurer->handCount();
        }
        $this->gmTotalHand += $this->gm->handCount();
    }
}
