<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\PreProcessable;
use PhpSchool\PhpWorkshop\Patch;
use PhpSchool\PhpWorkshopTest\Asset\PatchableExercise;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\CodeModification as M;

/**
 * Class CodePatcherTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodePatcherTest extends PHPUnit_Framework_TestCase
{
    public function testDefaultPatchIsAppliedIfAvailable()
    {
        $patch = (new Patch)
            ->withModification(new M(M::TYPE_BEFORE, 'ini_set("display_errors", 1);'));

        $patcher = new CodePatcher((new ParserFactory)->create(ParserFactory::PREFER_PHP7), new Standard, $patch);
        $exercise = $this->getMock(ExerciseInterface::class);

        $expected = "<?php\n\nini_set('display_errors', 1);\n\$original = true;";
        $this->assertEquals($expected, $patcher->patch($exercise, '<?php $original = true;'));
    }
    
    
    public function testPatcherDoesNotApplyPatchIfNotPatchableExercise()
    {
        $patcher = new CodePatcher((new ParserFactory)->create(ParserFactory::PREFER_PHP7), new Standard);
        $exercise = $this->getMock(ExerciseInterface::class);

        $exercise
            ->expects($this->never())
            ->method('getPatch')
            ->will($this->returnValue(new Patch));
        
        $code = '<?php $original = true;';
        $this->assertEquals($code, $patcher->patch($exercise, $code));
    }
    
    /**
     * @dataProvider codeProvider
     *
     * @param string $code
     * @param Patch $patch
     * @param string $expectedResult
     */
    public function testPatcher($code, Patch $patch, $expectedResult)
    {
        $patcher = new CodePatcher((new ParserFactory)->create(ParserFactory::PREFER_PHP7), new Standard);
        
        $exercise = $this->getMock(PatchableExercise::class);
        
        $exercise
            ->expects($this->once())
            ->method('getPatch')
            ->will($this->returnValue($patch));
        
        $result = $patcher->patch($exercise, $code);
        $this->assertEquals($expectedResult, $result);
    }

    public function codeProvider()
    {
        return [
            'only-before-modification' => [
                '<?php $original = true;',
                (new Patch)->withModification(new M(M::TYPE_BEFORE, '$before = "here";')),
                "<?php\n\n\$before = 'here';\n\$original = true;"
            ],
            'only-after-modification' => [
                '<?php $original = true;',
                (new Patch)->withModification(new M(M::TYPE_AFTER, '$after = "here";')),
                "<?php\n\n\$original = true;\n\$after = 'here';"
            ],
            'before-and-after-modification' => [
                '<?php $original = true;',
                (new Patch)
                    ->withModification(new M(M::TYPE_BEFORE, '$before = "here";'))
                    ->withModification(new M(M::TYPE_AFTER, '$after = "here";')),
                "<?php\n\n\$before = 'here';\n\$original = true;\n\$after = 'here';"
            ],
            'not-parseable-before-modification' => [
                '<?php $original = true;',
                (new Patch)->withModification(new M(M::TYPE_BEFORE, '$before = "here"')), //no semicolon at the end
                "<?php\n\n\$original = true;"
            ],
            'include-open-php-tag-before-modification' => [
                '<?php $original = true;',
                (new Patch)->withModification(new M(M::TYPE_BEFORE, '<?php $before = "here";')),
                "<?php\n\n\$before = 'here';\n\$original = true;"
            ],
            'include-open-php-tag-before-modification2' => [
                '<?php $original = true;',
                (new Patch)->withModification(new M(M::TYPE_BEFORE, '    <?php $before = "here";')),
                "<?php\n\n\$before = 'here';\n\$original = true;"
            ],
            'transformer' => [
                '<?php $original = true;',
                (new Patch)
                    ->withTransformer(function (array $statements) {
                        return [
                            new TryCatch($statements, [new Catch_(new Name(\Exception::class), 'e', [])])
                        ];
                    }),
                "<?php\n\ntry {\n    \$original = true;\n} catch (Exception \$e) {\n}"
            ],
            'transformer-with-before-mode' => [
                '<?php $original = true;',
                (new Patch)
                    ->withModification(new M(M::TYPE_BEFORE, '$before = "here";'))
                    ->withTransformer(function (array $statements) {
                        return [
                            new TryCatch($statements, [new Catch_(new Name(\Exception::class), 'e', [])])
                        ];
                    }),
                "<?php\n\ntry {\n    \$before = 'here';\n    \$original = true;\n} catch (Exception \$e) {\n}"
            ],
        ];
    }
}
