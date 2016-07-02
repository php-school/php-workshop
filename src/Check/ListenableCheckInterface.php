<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Event\EventDispatcher;

/**
 * The interface for advanced listener checks which can execute logic at any dispatched event.
 *
 * @package PhpSchool\PhpWorkshop\Check
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface ListenableCheckInterface extends CheckInterface
{
    /**
     * Attach to events throughout the running/verifying process. Inject verifiers
     * and listeners.
     *
     * @param EventDispatcher $eventDispatcher
     */
    public function attach(EventDispatcher $eventDispatcher);
}
