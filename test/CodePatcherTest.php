<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Patch;
use PhpSchool\PhpWorkshopTest\Asset\PatchableExercise;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\CodeInsertion as Insertion;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class CodePatcherTest extends TestCase
{
    public function testDefaultPatchIsAppliedIfAvailable(): void
    {
        $patch = (new Patch())
            ->withInsertion(new Insertion(Insertion::TYPE_BEFORE, 'ini_set("display_errors", 1);'));

        $patcher = new CodePatcher(
            (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
            new Standard(),
            new NullLogger(),
            $patch
        );
        $exercise = $this->createMock(ExerciseInterface::class);

        $expected = "<?php\n\nini_set(\"display_errors\", 1);\n\$original = true;";
        $this->assertEquals($expected, $patcher->patch($exercise, '<?php $original = true;'));
    }

    public function testPatcherDoesNotApplyPatchIfNotPatchableExercise(): void
    {
        $patcher = new CodePatcher(
            (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
            new Standard(),
            new NullLogger()
        );
        $exercise = $this->createMock(ExerciseInterface::class);

        $code = '<?php $original = true;';
        $this->assertEquals($code, $patcher->patch($exercise, $code));
    }

    /**
     * @dataProvider codeProvider
     */
    public function testPatcher(string $code, Patch $patch, string $expectedResult): void
    {
        $patcher = new CodePatcher(
            (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
            new Standard(),
            new NullLogger()
        );
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
            'transformer-closure' => [
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
            'transformer-class' => [
                '<?php $original = true;',
                (new Patch())
                    ->withTransformer(new class implements Patch\Transformer {
                        public function transform(array $statements): array
                        {
                            return [
                                new TryCatch(
                                    $statements,
                                    [new Catch_([new Name(\Exception::class)], new Variable('e'), [])]
                                )
                            ];
                        }
                    }),
                "<?php\n\ntry {\n    \$original = true;\n} catch (Exception \$e) {\n}"
            ],
        ];
    }

    public function testBeforeInsertionInsertsAfterStrictTypesDeclaration(): void
    {
        $code = '<?php declare(strict_types=1); $original = true;';
        $patch = (new Patch())->withInsertion(new Insertion(Insertion::TYPE_BEFORE, '$before = "here";'));

        $patcher = new CodePatcher(
            (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
            new Standard(),
            new NullLogger()
        );

        $exercise = $this->createMock(PatchableExercise::class);

        $exercise
            ->expects($this->once())
            ->method('getPatch')
            ->willReturn($patch);

        $this->assertEquals(
            "<?php\n\ndeclare (strict_types=1);\n\$before = \"here\";\n\$original = true;",
            $patcher->patch($exercise, $code)
        );
    }

    public function testTransformerWithStrictTypes(): void
    {
        $code = '<?php declare(strict_types=1); $original = true;';
        $patch = (new Patch())
            ->withTransformer(function (array $statements) {
                return [
                    new TryCatch(
                        $statements,
                        [new Catch_([new Name(\Exception::class)], new Variable('e'), [])]
                    )
                ];
            });

        $patcher = new CodePatcher(
            (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
            new Standard(),
            new NullLogger()
        );

        $exercise = $this->createMock(PatchableExercise::class);

        $exercise
            ->expects($this->once())
            ->method('getPatch')
            ->willReturn($patch);

        $this->assertEquals(
            "<?php\n\ndeclare (strict_types=1);\ntry {\n    \$original = true;\n} catch (Exception \$e) {\n}",
            $patcher->patch($exercise, $code)
        );
    }

    public function testTransformerWhichAddsStrictTypesDoesNotResultInDoubleStrictTypesStatement(): void
    {
        $code = '<?php declare(strict_types=1); $original = true;';
        $patch = (new Patch())
            ->withTransformer(function (array $statements) {
                return [new \PhpParser\Node\Stmt\Declare_([
                    new DeclareDeclare(
                        new \PhpParser\Node\Identifier('strict_types'),
                        new LNumber(1)
                    )
                ])];
            });

        $patcher = new CodePatcher(
            (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
            new Standard(),
            new NullLogger()
        );

        $exercise = $this->createMock(PatchableExercise::class);

        $exercise
            ->expects($this->once())
            ->method('getPatch')
            ->willReturn($patch);

        $this->assertEquals(
            "<?php\n\ndeclare (strict_types=1);",
            $patcher->patch($exercise, $code)
        );
    }

    public function testAddingStrictTypesDeclareDoesNotBreakBeforeInsertion(): void
    {
        $code = '<?php $original = true;';
        $patch = (new Patch())
            ->withTransformer(function (array $statements) {
                return array_merge([new \PhpParser\Node\Stmt\Declare_([
                    new DeclareDeclare(
                        new \PhpParser\Node\Identifier('strict_types'),
                        new LNumber(1)
                    )
                ])], $statements);
            })
            ->withInsertion(new Insertion(Insertion::TYPE_BEFORE, '$before = "here";'));

        $patcher = new CodePatcher(
            (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
            new Standard(),
            new NullLogger()
        );

        $exercise = $this->createMock(PatchableExercise::class);

        $exercise
            ->expects($this->once())
            ->method('getPatch')
            ->willReturn($patch);

        $this->assertEquals(
            "<?php\n\ndeclare (strict_types=1);\n\$before = \"here\";\n\$original = true;",
            $patcher->patch($exercise, $code)
        );
    }

    public function testExceptionIsLoggedIfCodeIsNotParseable(): void
    {
        $patcher = new CodePatcher(
            (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
            new Standard(),
            $logger = $this->createMock(LoggerInterface::class)
        );

        $exercise = $this->createMock(PatchableExercise::class);

        $patch = (new Patch())->withInsertion(new Insertion(Insertion::TYPE_BEFORE, '$before = "here"'));

        $exercise
            ->expects($this->once())
            ->method('getPatch')
            ->willReturn($patch);

        $logger
            ->expects($this->once())
            ->method('critical')
            ->with(
                'Code Insertion could not be parsed: Syntax error, unexpected EOF on line 1',
                ['code' => '$before = "here"']
            );

        $this->assertEquals(
            "<?php\n\n\$original = true;",
            $patcher->patch($exercise, '<?php $original = true;')
        );
    }
}
