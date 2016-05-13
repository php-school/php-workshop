<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;

/**
 * Class UserStateSerializerTest
 * @package PhpSchool\PhpWorkshopTest
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class UserStateSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var string
     */
    private $tmpFile;

    /**
     * @var string
     */
    private $workshopName = 'My Workshop';

    /**
     * @var ExerciseRepository
     */
    private $exerciseRepository;

    public function setUp()
    {
        $this->tmpDir = sprintf('%s/%s/%s', sys_get_temp_dir(), $this->getName(), rand(1, 100));
        $this->tmpFile = sprintf('%s/.phpschool-save.json', $this->tmpDir);
        $this->exerciseRepository = new ExerciseRepository([]);
    }

    public function testIfDirNotExistsItIsCreated()
    {
        $this->assertFileNotExists($this->tmpDir);
        new UserStateSerializer($this->tmpDir, $this->workshopName, $this->exerciseRepository);
        $this->assertFileExists($this->tmpDir);
    }

    public function testConstructWhenFileExists()
    {
        mkdir($this->tmpDir, 0777, true);
        $this->assertFileExists($this->tmpDir);
        new UserStateSerializer($this->tmpDir, $this->workshopName, $this->exerciseRepository);
    }

    public function testSerializeEmptySate()
    {
        mkdir($this->tmpDir, 0777, true);
        $serializer = new UserStateSerializer($this->tmpDir, $this->workshopName, $this->exerciseRepository);

        $state = new UserState;

        $expected = json_encode([
            'My Workshop' => [
                'completed_exercises' => [],
                'current_exercise' => null,
            ]
        ]);

        $res = $serializer->serialize($state);
        $this->assertTrue($res > 0);
        $this->assertSame($expected, file_get_contents($this->tmpFile));
    }

    public function testSerialize()
    {
        mkdir($this->tmpDir, 0777, true);
        $serializer = new UserStateSerializer($this->tmpDir, $this->workshopName, $this->exerciseRepository);

        $state = new UserState(['exercise1'], 'exercise2');
        $serializer->serialize($state);

        $expected = json_encode([
            'My Workshop' => [
                'completed_exercises' => ['exercise1'],
                'current_exercise' => 'exercise2',
            ]
        ]);

        $res = $serializer->serialize($state);
        $this->assertTrue($res > 0);
        $this->assertSame($expected, file_get_contents($this->tmpFile));
    }

    public function testDeserializeNonExistingFile()
    {
        mkdir($this->tmpDir, 0777, true);
        $serializer = new UserStateSerializer($this->tmpDir, $this->workshopName, $this->exerciseRepository);
        $state = $serializer->deSerialize();
        $this->assertNull($state->getCurrentExercise());
        $this->assertEmpty($state->getCompletedExercises());
    }

    public function testDeserializeEmptyFile()
    {
        mkdir($this->tmpDir, 0777, true);
        file_put_contents($this->tmpFile, '');
        $serializer = new UserStateSerializer($this->tmpDir, $this->workshopName, $this->exerciseRepository);
        $state = $serializer->deSerialize();
        $this->assertNull($state->getCurrentExercise());
        $this->assertEmpty($state->getCompletedExercises());
    }

    public function testDeserializeNonValidJson()
    {
        mkdir($this->tmpDir, 0777, true);
        file_put_contents($this->tmpFile, 'yayayayayanotjson');
        $serializer = new UserStateSerializer($this->tmpDir, $this->workshopName, $this->exerciseRepository);
        $state = $serializer->deSerialize();
        $this->assertNull($state->getCurrentExercise());
        $this->assertEmpty($state->getCompletedExercises());
    }

    /**
     * @param array $data
     * @param array $expected
     * @dataProvider deserializerProvider
     */
    public function testDeserialize(array $data, array $expected)
    {
        mkdir($this->tmpDir, 0777, true);
        file_put_contents($this->tmpFile, json_encode($data));
        $serializer = new UserStateSerializer($this->tmpDir, $this->workshopName, $this->exerciseRepository);
        $state = $serializer->deSerialize();

        $this->assertEquals($expected['completed_exercises'], $state->getCompletedExercises());
        $this->assertEquals($expected['current_exercise'], $state->getCurrentExercise());

        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
        rmdir($this->tmpDir);
    }

    public function deserializerProvider()
    {
        return [
            'empty-array' => [
                [],
                ['completed_exercises' => [], 'current_exercise' => null]
            ],
            'no-data-should-return-defaults' => [
                ['My Workshop' => []],
                ['completed_exercises' => [], 'current_exercise' => null]
            ],
            'no-current-exercise-set' => [
                ['My Workshop' => ['completed_exercises' => []]],
                ['completed_exercises' => [], 'current_exercise' => null]
            ],
            'completed-exercise-not-array' => [
                ['My Workshop' => ['completed_exercises' => null, 'current_exercise' => null]],
                ['completed_exercises' => [], 'current_exercise' => null]
            ],
            'invalid-completed-exercise' => [
                ['My Workshop' => ['completed_exercises' => [null], 'current_exercise' => null]],
                ['completed_exercises' => [], 'current_exercise' => null]
            ],
            'completed-exercises-no-current-exercise' => [
                ['My Workshop' => ['completed_exercises' => ['exercise1']]],
                ['completed_exercises' => [], 'current_exercise' => null]
            ],
            'completed-exercise-invalid-current-exercise' => [
                ['My Workshop' => ['completed_exercises' => ['exercise1'], 'current_exercise' => new \stdClass]],
                ['completed_exercises' => ['exercise1'], 'current_exercise' => null]
            ],
            'completed-exercise-current-null' => [
                ['My Workshop' => ['completed_exercises' => ['exercise1'], 'current_exercise' => null]],
                ['completed_exercises' => ['exercise1'], 'current_exercise' => null]
            ],
            'completed-exercise-with-current' => [
                ['My Workshop' => ['completed_exercises' => ['exercise1'], 'current_exercise' => 'exercise2']],
                ['completed_exercises' => ['exercise1'], 'current_exercise' => 'exercise2']
            ]
        ];
    }

    public function testOldDataWillBeMigratedWhenInCorrectWorkshop()
    {
        $oldSave = sprintf('%s/.phpschool.json', $this->tmpDir);
        $newSave = sprintf('%s/.phpschool-save.json', $this->tmpDir);

        $e1 = $this->getMock(ExerciseInterface::class);
        $e2 = $this->getMock(ExerciseInterface::class);

        $e1->expects($this->once())->method('getName')->willReturn('Exercise 1');
        $e2->expects($this->once())->method('getName')->willReturn('Exercise 2');

        $repo = new ExerciseRepository([$e1, $e2]);

        $oldData = [
            'current_exercise' => 'Exercise 3',
            'completed_exercises' => ['Exercise 1', 'Exercise 2'],
        ];

        mkdir($this->tmpDir, 0777, true);
        file_put_contents($oldSave, json_encode($oldData));

        $serializer = new UserStateSerializer($this->tmpDir, $this->workshopName, $repo);
        $state = $serializer->deSerialize();

        $this->assertEquals(['Exercise 1', 'Exercise 2'], $state->getCompletedExercises());
        $this->assertEquals('Exercise 3', $state->getCurrentExercise());

        $expected = [
            'My Workshop' => [
                'current_exercise' => 'Exercise 3',
                'completed_exercises' => ['Exercise 1', 'Exercise 2'],
            ],
        ];

        $this->assertFileNotExists($oldSave);
        $this->assertFileExists($newSave);
        $this->assertEquals($expected, json_decode(file_get_contents($newSave), true));
    }

    public function testOldDataWillNotBeMigratedWhenNotInCorrectWorkshop()
    {
        $oldSave = sprintf('%s/.phpschool.json', $this->tmpDir);
        $newSave = sprintf('%s/.phpschool-save.json', $this->tmpDir);

        $e1 = $this->getMock(ExerciseInterface::class);
        $e2 = $this->getMock(ExerciseInterface::class);

        $e1->expects($this->once())->method('getName')->willReturn('Exercise 1');
        $e2->expects($this->once())->method('getName')->willReturn('Exercise 2');

        $repo = new ExerciseRepository([$e1, $e2]);

        $oldData = [
            'current_exercise' => 'Exercise 3',
            'completed_exercises' => ['Exercise 1 from a diff workshop', 'Exercise 2 from a diff workshop'],
        ];

        mkdir($this->tmpDir, 0777, true);
        file_put_contents($oldSave, json_encode($oldData));

        $serializer = new UserStateSerializer($this->tmpDir, $this->workshopName, $repo);
        $state = $serializer->deSerialize();

        $this->assertEquals([], $state->getCompletedExercises());
        $this->assertEquals(null, $state->getCurrentExercise());

        $this->assertFileExists($oldSave);
        $this->assertEquals($oldData, json_decode(file_get_contents($oldSave), true));
        $this->assertFileNotExists($newSave);

        unlink($oldSave);
    }

    public function testOldDataWillNotBeMigratedWhenNotInCorrectWorkshopWithOtherWorkshop()
    {
        $oldSave = sprintf('%s/.phpschool.json', $this->tmpDir);
        $newSave = sprintf('%s/.phpschool-save.json', $this->tmpDir);

        $e1 = $this->getMock(ExerciseInterface::class);
        $e2 = $this->getMock(ExerciseInterface::class);

        $e1->expects($this->once())->method('getName')->willReturn('Exercise 1');
        $e2->expects($this->once())->method('getName')->willReturn('Exercise 2');

        $repo = new ExerciseRepository([$e1, $e2]);

        $oldData = [
            'current_exercise' => 'Exercise 3',
            'completed_exercises' => ['Exercise 1 from a diff workshop', 'Exercise 2 from a diff workshop'],
        ];

        $newData = [
            'My Workshop' => [
                'current_exercise' => 'Exercise 2',
                'completed_exercises' => ['Exercise 1'],
            ],
        ];

        mkdir($this->tmpDir, 0777, true);
        file_put_contents($oldSave, json_encode($oldData));
        file_put_contents($newSave, json_encode($newData));

        $serializer = new UserStateSerializer($this->tmpDir, $this->workshopName, $repo);
        $state = $serializer->deSerialize();

        $this->assertEquals(['Exercise 1'], $state->getCompletedExercises());
        $this->assertEquals('Exercise 2', $state->getCurrentExercise());

        $expected = [
            'current_exercise' => 'Exercise 3',
            'completed_exercises' => ['Exercise 1 from a diff workshop', 'Exercise 2 from a diff workshop'],
        ];

        $this->assertFileExists($oldSave);
        $this->assertEquals($expected, json_decode(file_get_contents($oldSave), true));

        $this->assertFileExists($newSave);
        $this->assertEquals($newData, json_decode(file_get_contents($newSave), true));

        unlink($oldSave);
    }

    public function tearDown()
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }

        if (file_exists($this->tmpDir)) {
            rmdir($this->tmpDir);
        }
    }
}
