<?php

namespace PhpSchool\PhpWorkshop\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\CliMenuBuilder;
use PhpSchool\CliMenu\MenuItem\AsciiArtItem;
use PhpSchool\PhpWorkshop\Command\CreditsCommand;
use PhpSchool\PhpWorkshop\Command\HelpCommand;
use PhpSchool\PhpWorkshop\Command\MenuCommandInvoker;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRenderer;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\MenuItem\ResetProgress;
use PhpSchool\PhpWorkshop\UserStateSerializer;

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

        $builder = (new CliMenuBuilder)
            ->addLineBreak();

        if (null !== $c->get('workshopLogo')) {
            $builder->addAsciiArt($c->get('workshopLogo'), AsciiArtItem::POSITION_CENTER);
        }

        $builder
            ->addLineBreak('_')
            ->addLineBreak()
            ->addStaticItem('Exercises')
            ->addStaticItem('---------')
            ->addItems(array_map(function (ExerciseInterface $exercise) use ($exerciseRenderer, $userState) {
                return [
                    $exercise->getName(),
                    $exerciseRenderer,
                    $userState->completedExercise($exercise->getName())
                ];
            }, $exerciseRepository->findAll()))
            ->addLineBreak()
            ->addLineBreak('-')
            ->addLineBreak()
            ->addItem('HELP', new MenuCommandInvoker($c->get(HelpCommand::class)))
            ->addItem('CREDITS', new MenuCommandInvoker($c->get(CreditsCommand::class)))
            ->setExitButtonText('EXIT')
            ->setBackgroundColour($c->get('bgColour'))
            ->setForegroundColour($c->get('fgColour'))
            ->setWidth(70)
            ->setUnselectedMarker(' ')
            ->setSelectedMarker('↳')
            ->setItemExtra('[COMPLETED]');

        $subMenu = $builder
            ->addSubMenu('OPTIONS')
            ->addLineBreak();

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
            $builder->setTitle($c->get('workshopTitle'));
            $subMenu->setTitle($c->get('workshopTitle'));
        }

        return $builder->build();
    }
}
