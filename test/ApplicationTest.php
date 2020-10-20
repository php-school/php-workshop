<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Application;
use PHPUnit\Framework\TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
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
}
