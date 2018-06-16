<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Sandbox\Adventurer;
use App\Model\Sandbox\Game;
use App\Model\Sandbox\GameStats;
use App\Model\Sandbox\GM;
use App\Model\Sandbox\Parameters;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class SandboxController extends AbstractController
{
    public function index(Request $request)
    {
        $parameters = new Parameters();

        $form = $this->createFormBuilder($parameters)
            ->add('iterations', IntegerType::class)
            ->add('race', ChoiceType::class, [
                'choices' => array_combine(
                    array_keys($this->races()),
                    array_keys($this->races())
                ),
            ])
            ->add('numberOfAdventurers', IntegerType::class)
            ->add('gmDrawAtStart', IntegerType::class)
            ->add('gmCardsToDraw', IntegerType::class)
            ->add('adventurersDrawAtStart', IntegerType::class)
            ->add('adventurersCardsToDraw', IntegerType::class)
            ->add('run', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $start = time();

            $stats = new GameStats();
            for ($i = 0; $i < $parameters->iterations; $i++) {
                $adventurers = [];
                for ($j = 0; $j < $parameters->numberOfAdventurers; $j++) {
                    $race = $this->races()[$parameters->race];
                    $adventurers[] = new Adventurer(
                        $race[0],
                        $race[1],
                        $race[2],
                        $parameters->adventurersDrawAtStart,
                        $parameters->adventurersCardsToDraw
                    );
                }
                $game = new Game(
                    new GM(
                        $parameters->gmDrawAtStart,
                        $parameters->gmCardsToDraw
                    ),
                    $adventurers
                );
                $game->play();
                $stats->update($game);
            }

            return $this->render('sandbox/index.html.twig', [
                'form' => $form->createView(),
                'races' => $this->races(),
                'elapsedSeconds' => time() - $start,
                'stats' => $stats,
            ]);
        }

        return $this->render('sandbox/index.html.twig', [
            'form' => $form->createView(),
            'races' => $this->races(),
        ]);
    }

    private function races(): array
    {
        return [
            'human'     => [1, 1, 0],
            'elf'       => [1, 0, 1],
            'gnome'     => [0, 1, 1],
            'orc'       => [2, -2, 0],
            'hobbit'    => [-2, 2, 0],
        ];
    }
}
