<?php

namespace PhpSchool\PhpWorkshopTest\Patch;

use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpSchool\PhpWorkshop\Patch\WrapInTryCatch;
use PHPUnit\Framework\TestCase;

class WrapInTryCatchTest extends TestCase
{
    public function testStatementsAreWrappedInTryCatch(): void
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse("<?php echo 'Hello World';");

        $transformer = new WrapInTryCatch();
        $ast = $transformer->transform($ast);

        self::assertSame(
            "try {\n    echo 'Hello World';\n} catch (Exception \$e) {\n    echo \$e->getMessage();\n}",
            (new Standard())->prettyPrint($ast),
        );
    }

    public function testStatementsAreWrappedInTryCatchWithCustomExceptionClass(): void
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse("<?php echo 'Hello World';");

        $transformer = new WrapInTryCatch(\RuntimeException::class);
        $ast = $transformer->transform($ast);

        self::assertSame(
            "try {\n    echo 'Hello World';\n} catch (RuntimeException \$e) {\n    echo \$e->getMessage();\n}",
            (new Standard())->prettyPrint($ast),
        );
    }

    public function testStatementsAreWrappedInTryCatchWithStatements(): void
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse("<?php echo 'Hello World';");

        $transformer = new WrapInTryCatch(\RuntimeException::class, [new Echo_([new String_('You caught me!')])]);
        $ast = $transformer->transform($ast);

        self::assertSame(
            "try {\n    echo 'Hello World';\n} catch (RuntimeException \$e) {\n    echo 'You caught me!';\n}",
            (new Standard())->prettyPrint($ast),
        );
    }
}
