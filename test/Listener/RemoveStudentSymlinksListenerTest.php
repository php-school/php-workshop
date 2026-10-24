<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Listener\RemoveStudentSymlinksListener;
use PhpSchool\PhpWorkshopTest\BaseTest;

class RemoveStudentSymlinksListenerTest extends BaseTest
{
    public function testSymlinksAreRemovedAndEverythingElseIsKept(): void
    {
        $context = new TestContext();
        $context->createStudentSolutionDirectory();
        $context->importStudentFileFromString('<?php echo "hi";', 'solution.php');
        mkdir($context->getStudentExecutionDirectory() . '/src');
        $context->importStudentFileFromString('<?php', 'src/Hello.php');

        $outside = dirname($this->getTemporaryFile('secret.txt', 'secret'));

        $dir = $context->getStudentExecutionDirectory();
        symlink($outside . '/secret.txt', $dir . '/link.txt');
        symlink($outside, $dir . '/linked-dir');
        symlink($outside . '/secret.txt', $dir . '/src/nested-link.txt');

        (new RemoveStudentSymlinksListener())->__invoke(new ExerciseRunnerEvent('run.finish', $context));

        $this->assertFileDoesNotExist($dir . '/link.txt');
        $this->assertFalse(is_link($dir . '/linked-dir'));
        $this->assertFalse(is_link($dir . '/src/nested-link.txt'));
        $this->assertFileExists($dir . '/solution.php');
        $this->assertFileExists($dir . '/src/Hello.php');
        $this->assertSame('secret', file_get_contents($outside . '/secret.txt'));
    }
}
