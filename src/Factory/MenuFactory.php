<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Factory;

use PhpSchool\CliMenu\Action\ExitAction;
use PhpSchool\CliMenu\MenuItem\SelectableItem;
use PhpSchool\CliMenu\Style\SelectableStyle;
use PhpSchool\PhpWorkshop\UserState\Serializer;
use PhpSchool\PhpWorkshop\UserState\UserState;
use PhpSchool\Terminal\Terminal;
use Psr\Container\ContainerInterface;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\PhpWorkshop\Command\CreditsCommand;
use PhpSchool\PhpWorkshop\Command\HelpCommand;
use PhpSchool\PhpWorkshop\Command\MenuCommandInvoker;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\AbstractExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRenderer;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\MenuItem\ResetProgress;
use PhpSchool\PhpWorkshop\WorkshopType;

/**
 * Configure the menu
 */
class MenuFactory
{
    /**
     * @param ContainerInterface $c
     * @return CliMenu
     */
    public function __invoke(ContainerInterface $c): CliMenu
    {
        /** @var Serializer $userStateSerializer */
        $userStateSerializer = $c->get(Serializer::class);
        $userState = $userStateSerializer->deSerialize();
        /** @var ExerciseRepository $exerciseRepository */
        $exerciseRepository = $c->get(ExerciseRepository::class);
        /** @var ExerciseRenderer $exerciseRenderer */
        $exerciseRenderer = $c->get(ExerciseRenderer::class);
        /** @var WorkshopType $workshopType */
        $workshopType = $c->get(WorkshopType::class);
        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $c->get(EventDispatcher::class);

        /** @var Terminal $terminal */
        $terminal = $c->get(Terminal::class);
        $builder = (new CliMenuBuilder($terminal))
            ->addLineBreak();

        $logo = $c->get('workshopLogo');

        if (!is_string($logo)) {
            $logo = null;
        }

        if ($logo) {
            $builder->addAsciiArt($logo);
        }

        $builder
            ->addLineBreak('_')
            ->addLineBreak()
            ->addStaticItem('Exercises')
            ->addStaticItem('---------');

        foreach ($exerciseRepository->findAll() as $exercise) {
            $builder->addItem(
                $exercise->getName(),
                function (CliMenu $menu) use ($exerciseRenderer, $eventDispatcher, $exercise) {
                    $menu->close();
                    $this->dispatchExerciseSelectedEvent($eventDispatcher, $exercise);
                    $exerciseRenderer->__invoke($menu);
                },
                $userState->completedExercise($exercise->getName()),
                $this->isExerciseDisabled($exercise, $userState, $workshopType),
            );
        }
        /** @var HelpCommand $helpCommand */
        $helpCommand = $c->get(HelpCommand::class);
        /** @var CreditsCommand $creditsCommand */
        $creditsCommand = $c->get(CreditsCommand::class);
        $builder
            ->addLineBreak()
            ->addLineBreak('-')
            ->addLineBreak()
            ->addItem('HELP', new MenuCommandInvoker($helpCommand))
            ->addItem('CREDITS', new MenuCommandInvoker($creditsCommand))
            ->disableDefaultItems()
            ->setBackgroundColour(is_string($bg = $c->get('bgColour')) ? $bg : 'black')
            ->setForegroundColour(is_string($fg = $c->get('fgColour')) ? $fg : 'magenta')
            ->setMarginAuto()
            ->setWidth(70)
            ->modifySelectableStyle(function (SelectableStyle $style) {
                $style
                    ->setUnselectedMarker('  ')
                    ->setSelectedMarker('↳ ');
            })
            ->setItemExtra('[COMPLETED]');

        $builder
            ->addSubMenu('OPTIONS', function (CliMenuBuilder $subMenu) use ($c, $logo) {
                if ($logo) {
                    $subMenu->addAsciiArt($logo);
                }

                /** @var ResetProgress $reset */
                $reset = $c->get(ResetProgress::class);

                $subMenu
                    ->addLineBreak('_')
                    ->addLineBreak()
                    ->addStaticItem('Options')
                    ->addStaticItem('-------')
                    ->addItem('Reset workshop progress', $reset)
                    ->addLineBreak()
                    ->addLineBreak('-')
                    ->addLineBreak()
                    ->setGoBackButtonText('GO BACK')
                    ->setExitButtonText('EXIT');

                if (is_string($title = $c->get('workshopTitle'))) {
                    $subMenu->setTitle($title);
                }
            })
            ->addLineBreak();

        $builder->addMenuItem(new SelectableItem('EXIT', new ExitAction()));

        if (PHP_OS_FAMILY === 'Darwin') {
            $builder->addLineBreak();
            $builder->addItem('www.phpschool.io', function () {
                exec('open https://www.phpschool.io');
            });
        }

        if (is_string($title = $c->get('workshopTitle'))) {
            $builder->setTitle($title);
        }

        return $builder->build();
    }

    /**
     * @param ExerciseInterface $exercise
     * @param UserState $userState
     * @param WorkshopType $type
     * @return bool
     */
    private function isExerciseDisabled(ExerciseInterface $exercise, UserState $userState, WorkshopType $type): bool
    {
        static $previous = null;

        if (null === $previous || !$type->isTutorialMode()) {
            $previous = $exercise;
            return false;
        }

        if (in_array($previous->getName(), $userState->getCompletedExercises(), true)) {
            $previous = $exercise;
            return false;
        }

        $previous = $exercise;
        return true;
    }

    /**
     * @param EventDispatcher $eventDispatcher
     * @param ExerciseInterface $exercise
     */
    private function dispatchExerciseSelectedEvent(EventDispatcher $eventDispatcher, ExerciseInterface $exercise): void
    {
        $eventDispatcher->dispatch(new Event('exercise.selected', ['exercise' => $exercise]));
        $eventDispatcher->dispatch(
            new Event(sprintf('exercise.selected.%s', AbstractExercise::normaliseName($exercise->getName()))),
        );
    }
}
