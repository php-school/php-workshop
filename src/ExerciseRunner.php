<?php

namespace PhpSchool\PhpWorkshop;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\SelfCheck;
use PhpSchool\PhpWorkshop\Result\FailureInterface;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use Symfony\Component\Process\Process;

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
     * Locations for composer executable
     * 
     * @var array
     */
    private $composerLocations = [
        'composer',
        'composer.phar',
        '/usr/local/bin/composer',
    ];
    
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
        $this->prepareSolution($exercise->getSolution());
        
        $resultAggregator = new ResultAggregator;
        
        //run pre-checks (before patching)
        foreach ($this->preChecks as $check) {
            if ($this->shouldRunCheck($check, $exercise, $this->preCheckMap)) {
                $result = $check->check($exercise, $fileName);
                $resultAggregator->add($result);

                if ($result instanceof FailureInterface) {
                    return $resultAggregator;
                }
            }
        }

        //patch code
        //pre-check takes care of checking that code can be parsed correctly
        //if not it would have returned already with a failure
        $originalCode = file_get_contents($fileName);
        file_put_contents($fileName, $this->codePatcher->patch($exercise, $originalCode));
        
        //run after checks (verifying output and content)
        foreach ($this->checks as $check) {
            if ($this->shouldRunCheck($check, $exercise, $this->checkMap)) {
                $resultAggregator->add($check->check($exercise, $fileName));
            }
        }

        //self check, for custom checking
        if ($exercise instanceof SelfCheck) {
            $resultAggregator->add($exercise->check($fileName));
        }

        $exercise->tearDown();
        
        //put back actual code, to remove patched additions
        file_put_contents($fileName, $originalCode);
        
        return $resultAggregator;
    }

    /**
     * @param CheckInterface $check
     * @param ExerciseInterface $exercise
     * @param array $checkMap
     * @return bool
     */
    private function shouldRunCheck(CheckInterface $check, ExerciseInterface $exercise, array $checkMap)
    {
        $exerciseInterface = $checkMap[spl_object_hash($check)];
        return is_subclass_of($exercise, $exerciseInterface);
    }

    /**
     * @param SolutionInterface $solution
     */
    private function prepareSolution(SolutionInterface $solution)
    {
        if ($solution->hasComposerFile()) {
            //prepare composer deps
            //only install if composer.lock file not available

            if (!file_exists(sprintf('%s/vendor', $solution->getBaseDirectory()))) {
                $process = new Process(
                    sprintf('%s install --no-interaction', $this->locateComposer()), 
                    $solution->getBaseDirectory()
                );
                $process->run();
            }
        }
    }

    /**
     * @return string
     */
    private function locateComposer()
    {
        foreach ($this->composerLocations as $location) {
            if (file_exists($location) && is_executable($location)) {
                return $location;
            }
        }
        
        throw new \RuntimeException('Composer could not be located on the system');
    }
}
