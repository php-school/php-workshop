<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\CodeInsertion;
use PhpSchool\PhpWorkshop\Patch;
use PHPUnit\Framework\TestCase;

class PatchTest extends TestCase
{
    public function testWithInsertion(): void
    {
        $patch = new Patch();
        $insertion = new CodeInsertion(CodeInsertion::TYPE_BEFORE, 'MEH');
        $new = $patch->withInsertion($insertion);

        $this->assertNotSame($patch, $new);
        $this->assertEmpty($patch->getModifiers());
        $this->assertEquals([$insertion], $new->getModifiers());
    }

    public function testWithTransformerWithClosure(): void
    {
        $patch = new Patch();
        $transformer = function (array $statements) {
            return $statements;
        };
        $new = $patch->withTransformer($transformer);
        $this->assertNotSame($patch, $new);
        $this->assertEmpty($patch->getModifiers());
        $this->assertEquals([$transformer], $new->getModifiers());
    }

    public function testWithTransformerWithTransformer(): void
    {
        $patch = new Patch();
        $transformer = new class implements Patch\Transformer {
            public function transform(array $ast): array
            {
                return $ast;
            }
        };

        $new = $patch->withTransformer($transformer);
        $this->assertNotSame($patch, $new);
        $this->assertEmpty($patch->getModifiers());
        $this->assertEquals([$transformer], $new->getModifiers());
    }

    public function testWithTransformerMultiple(): void
    {
        $transformer1 = new class implements Patch\Transformer {
            public function transform(array $ast): array
            {
                return $ast;
            }
        };
        $transformer2 = function (array $statements) {
            return $statements;
        };

        $patch = new Patch();
        $new = $patch
            ->withTransformer($transformer1)
            ->withTransformer($transformer2);

        $this->assertNotSame($patch, $new);
        $this->assertEmpty($patch->getModifiers());
        $this->assertEquals([$transformer1, $transformer2], $new->getModifiers());
    }
}
