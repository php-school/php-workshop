<?php

namespace PhpSchool\PhpWorkshop;

use PhpParser\Error;
use PhpParser\Parser;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\PreProcessable;
use PhpSchool\PhpWorkshop\ExerciseCheck\SelfCheck;
use PhpSchool\PhpWorkshop\Result\FailureInterface;

/**
 * Class ExerciseRunner
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseRunner
{
    /**
     * @var CodePatcher
     */
    private $codePatcher;
    
    /**
     * @var CheckInterface[]
     */
    private $checks = [];

    /**
     * @var array
     */
    private $checkMap = [];

    /**
     * @var CheckInterface[]
     */
    private $preChecks = [];

    /**
     * @var array
     */
    private $preCheckMap = [];

    /**
     * @param CodePatcher $codePatcher
     */
    public function __construct(CodePatcher $codePatcher)
    {
        $this->codePatcher = $codePatcher;
    }

    /**
     * @param CheckInterface $check
     * @param string $exerciseInterface
     */
    public function registerPreCheck(CheckInterface $check, $exerciseInterface = null)
    {
        if (null !== $exerciseInterface && !is_string($exerciseInterface)) {
            throw InvalidArgumentException::typeMisMatch('string', $exerciseInterface);
        }

        $lookUp                     = spl_object_hash($check);
        $this->preChecks[$lookUp]   = $check;
        $this->preCheckMap[$lookUp] = $exerciseInterface;
    }
    
    /**
     * @param CheckInterface $check
     * @param string $exerciseInterface
     */
    public function registerCheck(CheckInterface $check, $exerciseInterface = null)
    {
        if (null !== $exerciseInterface && !is_string($exerciseInterface)) {
            throw InvalidArgumentException::typeMisMatch('string', $exerciseInterface);
        }

        $lookUp                     = spl_object_hash($check);
        $this->checks[$lookUp]      = $check;
        $this->checkMap[$lookUp]    = $exerciseInterface;
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultAggregator
     */
    public function runExercise(ExerciseInterface $exercise, $fileName)
    {
        $resultAggregator = new ResultAggregator;
        
        //run pre-checks (before patching)
        foreach ($this->preChecks as $check) {
            $exerciseInterface = $this->preCheckMap[spl_object_hash($check)];

            if (!is_subclass_of($exercise, $exerciseInterface)) {
                continue;
            }

            $result = $check->check($exercise, $fileName);
            $resultAggregator->add($result);
            
            if ($result instanceof FailureInterface) {
                return $resultAggregator;
            }
        }
        
        //patch code, maybe
        if ($exercise instanceof PreProcessable) {
            $oldContent = file_get_contents($fileName);
            
            //pre-check takes care of checking that code can be parsed correctly
            //if not it would have returned already with a failure
            $code = $this->codePatcher->patch($oldContent, $exercise->getModifications());
            file_put_contents($fileName, $code);
        }

        //run after checks (verifying output and content)
        foreach ($this->checks as $check) {
            $exerciseInterface = $this->checkMap[spl_object_hash($check)];

            if (!is_subclass_of($exercise, $exerciseInterface)) {
                continue;
            }

            $result = $check->check($exercise, $fileName);
            $resultAggregator->add($result);
        }

        //self check, for custom checking
        if ($exercise instanceof SelfCheck) {
            $resultAggregator->add($exercise->check($fileName));
        }

        $exercise->tearDown();
        
        //put back actual code, to remove patched additions
        if ($exercise instanceof PreProcessable) {
            file_put_contents($fileName, $oldContent);
        }
        
        return $resultAggregator;
    }
}
