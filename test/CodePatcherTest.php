<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpSchool\PhpWorkshop\CodeModification;
use PhpSchool\PhpWorkshop\CodePatcher;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\CodeModification as M;

/**
 * Class CodePatcherTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodePatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider codeProvider
     *
     * @param string $code
     * @param CodeModification[] $modifications
     * @param string $expectedResult
     */
    public function testPatcher($code, array $modifications, $expectedResult)
    {
        $patcher = new CodePatcher((new ParserFactory)->create(ParserFactory::PREFER_PHP7), new Standard);
        
        $result = $patcher->patch($code, $modifications, $expectedResult);
        $this->assertEquals($expectedResult, $result);
    }

    public function codeProvider()
    {
        return [
            'only-before-modification' => [
                '<?php $original = true;',
                [new M(M::TYPE_BEFORE, '$before = "here";')],
                "<?php \$before = 'here';\n\$original = true;"
            ],
            'only-after-modification' => [
                '<?php $original = true;',
                [new M(M::TYPE_AFTER, '$after = "here";')],
                "<?php \$original = true;\n\$after = 'here';"
            ],
            'before-and-after-modification' => [
                '<?php $original = true;',
                [new M(M::TYPE_BEFORE, '$before = "here";'), new M(M::TYPE_AFTER, '$after = "here";')],
                "<?php \$before = 'here';\n\$original = true;\n\$after = 'here';"
            ],
            'not-parseable-before-modification' => [
                '<?php $original = true;',
                [new M(M::TYPE_BEFORE, '$before = "here"')], //no semicolon at the end
                "<?php \$original = true;"
            ],
            'include-open-php-tag-before-modification' => [
                '<?php $original = true;',
                [new M(M::TYPE_BEFORE, '<?php $before = "here";')],
                "<?php \$before = 'here';\n\$original = true;"
            ],
            'include-open-php-tag-before-modification2' => [
                '<?php $original = true;',
                [new M(M::TYPE_BEFORE, '    <?php $before = "here";')],
                "<?php \$before = 'here';\n\$original = true;"
            ],
        ];
    }
}
