<?php

namespace PhpSchool\PhpWorkshop\Result;

/**
 * Class CgiOutHeadersFailure
 * @package PhpSchool\PhpWorkshop\Result
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutHeadersFailure extends Failure
{
    /**
     * @var array
     */
    private $expectedHeaders;
    
    /**
     * @var array
     */
    private $actualHeaders;

    /**
     * @param array $expectedHeaders
     * @param array $actualHeaders
     */
    public function __construct(array $expectedHeaders, array $actualHeaders)
    {
        $this->expectedHeaders = $expectedHeaders;
        $this->actualHeaders = $actualHeaders;
        
        $reason = sprintf('Headers did not match.');
        parent::__construct('Cgi Headers', $reason);
    }

    /**
     * @return array
     */
    public function getExpectedHeaders()
    {
        return $this->expectedHeaders;
    }

    /**
     * @return array
     */
    public function getActualHeaders()
    {
        return $this->actualHeaders;
    }
}
