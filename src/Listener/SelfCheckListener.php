<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\ExerciseCheck\SelfCheck;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\ResultAggregator;

/**
 * Listener to handle exercises which inject their own checks
 */
class SelfCheckListener
{
    /**
     * @var ResultAggregator
     */
    private $results;

    /**
     * @param ResultAggregator $results
     */
    public function __construct(ResultAggregator $results)
    {
        $this->results = $results;
    }

    /**
     * @param Event $event
     */
    public function __invoke(Event $event): void
    {
        $exercise = $event->getParameter('exercise');

        if ($exercise instanceof SelfCheck) {
            /** @var Input $input */
            $input = $event->getParameter('input');
            $this->results->add($exercise->check($input));
        }
    }
}
