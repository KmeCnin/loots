<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlaceRepository")
 */
class Place
{
    public const TYPE_BOSS = 'boss';
    public const TYPE_SAFE = 'safe';
    public const TYPE_FIGHT = 'fight';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $slots;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $effect;

    public function __construct()
    {
        $this->setType(self::TYPE_FIGHT);
        $this->setName('');
        $this->setSlots(2);
        $this->setEffect('');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function availableTypes(): array
    {
        return [
            self::TYPE_BOSS,
            self::TYPE_SAFE,
            self::TYPE_FIGHT,
        ];
    }

    public function setType(string $type): self
    {
        if (!\in_array($type, $this->availableTypes())) {
            throw new \Exception();
        }

        $this->type = $type;

        return $this;
    }

    public function getSlots(): int
    {
        return $this->slots;
    }

    public function setSlots(int $numberOfSlots): self
    {
        $this->slots = $numberOfSlots;

        return $this;
    }

    public function getEffect(): string
    {
        return $this->effect;
    }

    public function setEffect(string $effect): self
    {
        $this->effect = $effect;

        return $this;
    }
}
