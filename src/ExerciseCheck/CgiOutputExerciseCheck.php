<?php

namespace PhpSchool\PhpWorkshop\ExerciseCheck;

use Psr\Http\Message\RequestInterface;

/**
 * Interface CgiOutputExerciseCheck
 * @package PhpSchool\PhpWorkshop\ExerciseCheck
 */
interface CgiOutputExerciseCheck
{
    /**
     * @var string
     */
    const METHOD_GET = 'GET';

    /**
     * @var string
     */
    const METHOD_POST = 'POST';

    /**
     * @var string
     */
    const METHOD_PUT = 'PUT';

    /**
     * @var string
     */
    const METHOD_PATCH = 'PATCH';

    /**
     * @var string
     */
    const METHOD_DELETE = 'DELETE';
    
    /**
     * @return RequestInterface
     */
    public function getRequest();
}
