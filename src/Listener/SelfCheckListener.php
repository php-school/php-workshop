<?php

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\ExerciseCheck\SelfCheck;
use PhpSchool\PhpWorkshop\ResultAggregator;

/**
 * Class SelfCheckListener
 * @package PhpSchool\PhpWorkshopTest\Listener
 * @author Aydin Hassan <aydin@hotmail.co.uk>
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
    public function __invoke(Event $event)
    {
        $exercise = $event->getParameter('exercise');

        if ($exercise instanceof SelfCheck) {
            $this->results->add($exercise->check($event->getParameter('fileName')));
        }
    }
}
