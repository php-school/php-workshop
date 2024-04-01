<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\ExerciseCheck\SelfCheck;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\ResultAggregator;

/**
 * Listener to handle exercises which inject their own checks
 */
class SelfCheckListener
{
    private ResultAggregator $results;

    /**
     * @param ResultAggregator $results
     */
    public function __construct(ResultAggregator $results)
    {
        $this->results = $results;
    }

    public function __invoke(ExerciseRunnerEvent $event): void
    {
        $exercise = $event->getParameter('exercise');

        if ($exercise instanceof SelfCheck) {
            $this->results->add($exercise->check($event->context->getExecutionContext()));
        }
    }
}
