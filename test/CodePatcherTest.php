<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Patch;
use PhpSchool\PhpWorkshopTest\Asset\PatchableExercise;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\CodeInsertion as Insertion;

class CodePatcherTest extends TestCase
{
    public function testDefaultPatchIsAppliedIfAvailable(): void
    {
        $patch = (new Patch())
            ->withInsertion(new Insertion(Insertion::TYPE_BEFORE, 'ini_set("display_errors", 1);'));

        $patcher = new CodePatcher((new ParserFactory())->create(ParserFactory::PREFER_PHP7), new Standard(), $patch);
        $exercise = $this->createMock(ExerciseInterface::class);

        $expected = "<?php\n\nini_set(\"display_errors\", 1);\n\$original = true;";
        $this->assertEquals($expected, $patcher->patch($exercise, '<?php $original = true;'));
    }


    public function testPatcherDoesNotApplyPatchIfNotPatchableExercise(): void
    {
        $patcher = new CodePatcher((new ParserFactory())->create(ParserFactory::PREFER_PHP7), new Standard());
        $exercise = $this->createMock(ExerciseInterface::class);

        $code = '<?php $original = true;';
        $this->assertEquals($code, $patcher->patch($exercise, $code));
    }

    /**
     * @dataProvider codeProvider
     */
    public function testPatcher(string $code, Patch $patch, string $expectedResult): void
    {
        $patcher = new CodePatcher((new ParserFactory())->create(ParserFactory::PREFER_PHP7), new Standard());

        $exercise = $this->createMock(PatchableExercise::class);

        $exercise
            ->expects($this->once())
            ->method('getPatch')
            ->willReturn($patch);

        $result = $patcher->patch($exercise, $code);
        $this->assertEquals($expectedResult, $result);
    }

    public function codeProvider(): array
    {
        return [
            'only-before-insertion' => [
                '<?php $original = true;',
                (new Patch())->withInsertion(new Insertion(Insertion::TYPE_BEFORE, '$before = "here";')),
                "<?php\n\n\$before = \"here\";\n\$original = true;"
            ],
            'only-after-insertion' => [
                '<?php $original = true;',
                (new Patch())->withInsertion(new Insertion(Insertion::TYPE_AFTER, '$after = "here";')),
                "<?php\n\n\$original = true;\n\$after = \"here\";"
            ],
            'before-and-after-insertion' => [
                '<?php $original = true;',
                (new Patch())
                    ->withInsertion(new Insertion(Insertion::TYPE_BEFORE, '$before = "here";'))
                    ->withInsertion(new Insertion(Insertion::TYPE_AFTER, '$after = "here";')),
                "<?php\n\n\$before = \"here\";\n\$original = true;\n\$after = \"here\";"
            ],
            'not-parseable-before-insertion' => [
                '<?php $original = true;',
                (new Patch())->withInsertion(new Insertion(Insertion::TYPE_BEFORE, '$before = "here"')),
                //no semicolon at the end
                "<?php\n\n\$original = true;"
            ],
            'include-open-php-tag-before-insertion' => [
                '<?php $original = true;',
                (new Patch())->withInsertion(new Insertion(Insertion::TYPE_BEFORE, '<?php $before = "here";')),
                "<?php\n\n\$before = \"here\";\n\$original = true;"
            ],
            'include-open-php-tag-before-insertion2' => [
                '<?php $original = true;',
                (new Patch())->withInsertion(new Insertion(Insertion::TYPE_BEFORE, '    <?php $before = "here";')),
                "<?php\n\n\$before = \"here\";\n\$original = true;"
            ],
            'transformer' => [
                '<?php $original = true;',
                (new Patch())
                    ->withTransformer(function (array $statements) {
                        return [
                            new TryCatch(
                                $statements,
                                [new Catch_([new Name(\Exception::class)], new Variable('e'), [])]
                            )
                        ];
                    }),
                "<?php\n\ntry {\n    \$original = true;\n} catch (Exception \$e) {\n}"
            ],
            'transformer-with-before-insertion' => [
                '<?php $original = true;',
                (new Patch())
                    ->withInsertion(new Insertion(Insertion::TYPE_BEFORE, '$before = "here";'))
                    ->withTransformer(function (array $statements) {
                        return [
                            new TryCatch(
                                $statements,
                                [new Catch_([new Name(\Exception::class)], new Variable('e'), [])]
                            )
                        ];
                    }),
                "<?php\n\ntry {\n    \$before = \"here\";\n    \$original = true;\n} catch (Exception \$e) {\n}"
            ],
        ];
    }
}
