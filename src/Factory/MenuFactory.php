<?php

namespace PhpSchool\PhpWorkshop\Factory;

use PhpSchool\CliMenu\Style\SelectableStyle;
use Psr\Container\ContainerInterface;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\MenuItem\AsciiArtItem;
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
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;
use PhpSchool\PhpWorkshop\WorkshopType;

/**
 * Class MenuFactory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MenuFactory
{
    /**
     * @param ContainerInterface $c
     * @return CliMenu
     */
    public function __invoke(ContainerInterface $c)
    {
        $userStateSerializer    = $c->get(UserStateSerializer::class);
        $exerciseRepository     = $c->get(ExerciseRepository::class);
        $userState              = $userStateSerializer->deSerialize();
        $exerciseRenderer       = $c->get(ExerciseRenderer::class);
        $workshopType           = $c->get(WorkshopType::class);
        $eventDispatcher        = $c->get(EventDispatcher::class);

        $builder = (new CliMenuBuilder)
            ->addLineBreak();

        if (null !== $c->get('workshopLogo')) {
            $builder->addAsciiArt($c->get('workshopLogo'), AsciiArtItem::POSITION_CENTER);
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
                    $this->dispatchExerciseSelectedEvent($eventDispatcher, $exercise);
                    $exerciseRenderer->__invoke($menu);
                },
                $userState->completedExercise($exercise->getName()),
                $this->isExerciseDisabled($exercise, $userState, $workshopType)
            );
        }

        $builder
            ->addLineBreak()
            ->addLineBreak('-')
            ->addLineBreak()
            ->addItem('HELP', new MenuCommandInvoker($c->get(HelpCommand::class)))
            ->addItem('CREDITS', new MenuCommandInvoker($c->get(CreditsCommand::class)))
            ->setExitButtonText('EXIT')
            ->setBackgroundColour($c->get('bgColour'))
            ->setForegroundColour($c->get('fgColour'))
            ->setWidth(70)
            ->modifySelectableStyle(function (SelectableStyle $style) {
                $style
                    ->setUnselectedMarker('  ')
                    ->setSelectedMarker('↳');
            })
            ->setItemExtra('[COMPLETED]');

        $builder
            ->addSubMenu('OPTIONS', function (CliMenuBuilder $subMenu) use ($c) {
                if (null !== $c->get('workshopLogo')) {
                    $subMenu->addAsciiArt($c->get('workshopLogo'), AsciiArtItem::POSITION_CENTER);
                }

                $subMenu
                    ->addLineBreak('_')
                    ->addLineBreak()
                    ->addStaticItem('Options')
                    ->addStaticItem('-------')
                    ->addItem('Reset workshop progress', $c->get(ResetProgress::class))
                    ->addLineBreak()
                    ->addLineBreak('-')
                    ->addLineBreak()
                    ->setGoBackButtonText('GO BACK')
                    ->setExitButtonText('EXIT');

                if (null !== $c->get('workshopTitle')) {
                    $subMenu->setTitle($c->get('workshopTitle'));
                }
            })
            ->addLineBreak();


        if (null !== $c->get('workshopTitle')) {
            $builder->setTitle($c->get('workshopTitle'));
        }

        return $builder->build();
    }

    /**
     * @param ExerciseInterface $exercise
     * @param UserState         $userState
     * @param WorkshopType      $type
     * @return bool
     */
    private function isExerciseDisabled(ExerciseInterface $exercise, UserState $userState, WorkshopType $type)
    {
        static $previous = null;

        if (null === $previous || !$type->isTutorialMode()) {
            $previous = $exercise;
            return false;
        }

        if (in_array($previous->getName(), $userState->getCompletedExercises())) {
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
    private function dispatchExerciseSelectedEvent(EventDispatcher $eventDispatcher, ExerciseInterface $exercise)
    {
        $eventDispatcher->dispatch(
            new Event(
                sprintf(
                    'exercise.selected.%s',
                    AbstractExercise::normaliseName($exercise->getName())
                )
            )
        );
    }
}
