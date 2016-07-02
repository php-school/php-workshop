<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\ResultAggregator;

/**
 * A result which encompasses all the results for each individual request made during
 * the CGI verification process.
 *
 * @package PhpSchool\PhpWorkshop\Result
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutResult extends ResultAggregator implements ResultInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name The name of the check that produced this result.
     * @param array $requestResults An array of results representing each request.
     */
    public function __construct($name, array $requestResults)
    {
        $this->name = $name;
        foreach ($requestResults as $request) {
            $this->add($request);
        }
    }


    /**
     * Get the name of the check that this result was produced from, most likely the CGI Runner.
     *
     * @return string
     */
    public function getCheckName()
    {
        return $this->name;
    }
}
