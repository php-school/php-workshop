<?php

namespace PhpSchool\PhpWorkshopTest\Patch;

use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpSchool\PhpWorkshop\Patch\ForceStrictTypes;
use PHPUnit\Framework\TestCase;

class ForceStrictTypesTest extends TestCase
{
    public function testStrictTypesDeclareIsAppended(): void
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse("<?php echo 'Hello World';");

        $transformer = new ForceStrictTypes();
        $ast = $transformer->transform($ast);

        self::assertSame(
            "declare (strict_types=1);\necho 'Hello World';",
            (new Standard())->prettyPrint($ast),
        );
    }

    public function testStrictTypesDeclareIsNotAppendedIfItAlreadyExists(): void
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse("<?php declare (strict_types=1);\necho 'Hello World';");
        $transformer = new ForceStrictTypes();

        $ast = $transformer->transform($ast);

        self::assertSame(
            "declare (strict_types=1);\necho 'Hello World';",
            (new Standard())->prettyPrint($ast),
        );
    }
}
