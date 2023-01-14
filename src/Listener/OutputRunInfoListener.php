<?php

namespace PhpSchool\PhpWorkshop\Listener;

use PhpSchool\PhpWorkshop\Event\CgiExecuteEvent;
use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Utils\RequestRenderer;

class OutputRunInfoListener
{
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var RequestRenderer
     */
    private $requestRenderer;

    public function __construct(OutputInterface $output, RequestRenderer $requestRenderer)
    {
        $this->output = $output;
        $this->requestRenderer = $requestRenderer;
    }

    public function __invoke(Event $event): void
    {
        if (PHP_SAPI !== "cli") {
            return;
        }

        switch (get_class($event)) {
            case CliExecuteEvent::class:
                $args = $event->getArgs();

                if (count($args)) {
                    $glue = max(array_map('strlen', $args->getArrayCopy())) > 30 ? "\n" : ', ';
                    $this->output->writeTitle('Arguments');
                    $this->output->write(implode($glue, $args->getArrayCopy()));
                    $this->output->emptyLine();
                }

                break;
           case CgiExecuteEvent::class:
               $request = $event->getRequest();

               $this->output->writeTitle("Request");
               $this->output->emptyLine();
               $this->output->write($this->requestRenderer->renderRequest($request));

                break;
            default:
                throw new RuntimeException('Unsupported event type');
        }

        $this->output->writeTitle("Output");
        $this->output->emptyLine();
    }
}