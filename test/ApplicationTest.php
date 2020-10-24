<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Application;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

class ApplicationTest extends TestCase
{
    public function testEventListenersFromLocalAndWorkshopConfigAreMerged(): void
    {

        $frameworkFileContent  = '<?php return [';
        $frameworkFileContent .= "    'eventListeners' => [";
        $frameworkFileContent .= "        'event1' => [";
        $frameworkFileContent .= "             'entry1',";
        $frameworkFileContent .= "             'entry2',";
        $frameworkFileContent .= '         ]';
        $frameworkFileContent .= '    ]';
        $frameworkFileContent .= '];';

        $localFileContent  = '<?php return [';
        $localFileContent .= "    'eventListeners' => [";
        $localFileContent .= "        'event1' => [";
        $localFileContent .= "             'entry3',";
        $localFileContent .= '         ]';
        $localFileContent .= '    ]';
        $localFileContent .= '];';

        $localFile = sprintf('%s/%s', sys_get_temp_dir(), uniqid($this->getName(), true));
        $frameworkFile = sprintf('%s/%s', sys_get_temp_dir(), uniqid($this->getName(), true));
        file_put_contents($frameworkFile, $frameworkFileContent);
        file_put_contents($localFile, $localFileContent);

        $app = new Application('Test App', $localFile);

        $rm = new \ReflectionMethod($app, 'getContainer');
        $rm->setAccessible(true);

        $rp = new \ReflectionProperty(Application::class, 'frameworkConfigLocation');
        $rp->setAccessible(true);
        $rp->setValue($app, $frameworkFile);

        $container = $rm->invoke($app);

        $eventListeners = $container->get('eventListeners');

        $this->assertEquals(
            [
                'event1' => [
                    'entry1',
                    'entry2',
                    'entry3',
                ]
            ],
            $eventListeners
        );
    }

    public function testExceptionIsThrownIfConfigFileDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File "not-existing-file.php" was expected to exist.');

        new Application('My workshop', 'not-existing-file.php');
    }

    public function testExceptionIsThrownIfResultClassDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class "NotExistingClass" does not exist');

        $app = new Application('My workshop', __DIR__ . '/../app/config.php');
        $app->addResult(\NotExistingClass::class, \NotExistingClass::class);
    }

    public function testExceptionIsThrownIfResultRendererClassDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class "NotExistingClass" does not exist');

        $app = new Application('My workshop', __DIR__ . '/../app/config.php');
        $app->addResult(\PhpSchool\PhpWorkshop\Result\Success::class, \NotExistingClass::class);
    }
}
