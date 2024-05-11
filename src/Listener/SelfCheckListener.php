<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\ExerciseCheck\SelfCheck;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\ResultAggregator;

/**
 * Listener to handle exercises which inject their own checks
 */
class SelfCheckListener
{
    public function __construct(private ResultAggregator $results) {}

    public function __invoke(ExerciseRunnerEvent $event): void
    {
        $exercise = $event->getContext()->getExercise();

        if ($exercise instanceof SelfCheck) {
            /** @var Input $input */
            $input = $event->getParameter('input');
            $this->results->add($exercise->check($event->getContext()));
        }
    }
}
