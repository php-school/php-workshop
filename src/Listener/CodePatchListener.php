<?php

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Event\Event;
use RuntimeException;

/**
 * Class CodePatchListener
 * @package PhpSchool\PhpWorkshop\Listener
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodePatchListener
{
    /**
     * @var CodePatcher
     */
    private $codePatcher;

    /**
     * @var string
     */
    private $originalCode;

    /**
     * CodePatchListener constructor.
     * @param CodePatcher $codePatcher
     */
    public function __construct(CodePatcher $codePatcher)
    {
        $this->codePatcher = $codePatcher;
    }

    /**
     * @param Event $event
     */
    public function patch(Event $event)
    {
        $fileName           = $event->getParameter('fileName');
        $this->originalCode = file_get_contents($fileName);
        file_put_contents(
            $fileName,
            $this->codePatcher->patch($event->getParameter('exercise'), $this->originalCode)
        );
    }

    /**
     * @param Event $event
     */
    public function revert(Event $event)
    {
        if (null === $this->originalCode) {
            throw new RuntimeException('Can only revert previously patched code');
        }

        file_put_contents($event->getParameter('fileName'), $this->originalCode);
    }
}
