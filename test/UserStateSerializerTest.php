<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshop\Utils\System;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseInterface;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class UserStateSerializerTest extends BaseTest
{
    use AssertionRenames;

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

    public function setUp(): void
    {
        $this->exerciseRepository = new ExerciseRepository([]);
    }

    public function testIfDirNotExistsItIsCreated(): void
    {
        self::assertFileDoesNotExist(System::tempDir($this->getName()));
        new UserStateSerializer(System::tempDir($this->getName()), $this->workshopName, $this->exerciseRepository);
        $this->assertFileExists(System::tempDir($this->getName()));
    }

    public function testConstructWhenFileExists(): void
    {
        $this->assertFileExists($this->getTemporaryDirectory());
        new UserStateSerializer($this->getTemporaryDirectory(), $this->workshopName, $this->exerciseRepository);
    }

    public function testSerializeEmptySate(): void
    {
        $serializer = new UserStateSerializer(
            $this->getTemporaryDirectory(),
            $this->workshopName,
            $this->exerciseRepository
        );

        $state = new UserState();

        $expected = json_encode([
            'My Workshop' => [
                'completed_exercises' => [],
                'current_exercise' => null,
            ]
        ]);

        $serializer->serialize($state);
        $this->assertSame($expected, file_get_contents($this->getTemporaryFile('.phpschool-save.json')));
    }

    public function testSerialize(): void
    {
        $serializer = new UserStateSerializer(
            $this->getTemporaryDirectory(),
            $this->workshopName,
            $this->exerciseRepository
        );

        $state = new UserState(['exercise1'], 'exercise2');
        $serializer->serialize($state);

        $expected = json_encode([
            'My Workshop' => [
                'completed_exercises' => ['exercise1'],
                'current_exercise' => 'exercise2',
            ]
        ]);

        $serializer->serialize($state);

        $this->assertSame($expected, file_get_contents($this->getTemporaryFile('.phpschool-save.json')));
    }

    public function testDeserializeNonExistingFile(): void
    {
        $serializer = new UserStateSerializer(
            $this->getTemporaryDirectory(),
            $this->workshopName,
            $this->exerciseRepository
        );

        $state = $serializer->deSerialize();
        $this->assertFalse($state->isAssignedExercise());
        $this->assertEmpty($state->getCompletedExercises());
    }

    public function testDeserializeEmptyFile(): void
    {
        $this->getTemporaryFile('.phpschool-save.json', '');
        $serializer = new UserStateSerializer(
            $this->getTemporaryDirectory(),
            $this->workshopName,
            $this->exerciseRepository
        );
        $state = $serializer->deSerialize();
        $this->assertFalse($state->isAssignedExercise());
        $this->assertEmpty($state->getCompletedExercises());
    }

    public function testDeserializeNonValidJson(): void
    {
        $this->getTemporaryFile('.phpschool-save.json', 'yayayayayanotjson');
        $serializer = new UserStateSerializer(
            $this->getTemporaryDirectory(),
            $this->workshopName,
            $this->exerciseRepository
        );
        $state = $serializer->deSerialize();
        $this->assertFalse($state->isAssignedExercise());
        $this->assertEmpty($state->getCompletedExercises());
    }

    /**
     * @dataProvider deserializerProvider
     */
    public function testDeserialize(array $data, array $expected): void
    {
        $this->getTemporaryFile('.phpschool-save.json', json_encode($data));
        $serializer = new UserStateSerializer(
            $this->getTemporaryDirectory(),
            $this->workshopName,
            $this->exerciseRepository
        );
        $state = $serializer->deSerialize();

        $this->assertEquals($expected['completed_exercises'], $state->getCompletedExercises());
        $this->assertEquals(
            $expected['current_exercise'],
            $state->isAssignedExercise() ? $state->getCurrentExercise() : null
        );
    }

    public function deserializerProvider(): array
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
                ['My Workshop' => ['completed_exercises' => ['exercise1'], 'current_exercise' => new \stdClass()]],
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

    public function testOldDataWillBeMigratedWhenInCorrectWorkshop(): void
    {
        $exercise1 = $this->createMock(CliExerciseInterface::class);
        $exercise2 = $this->createMock(CliExerciseInterface::class);
        $exercise1->method('getType')->willReturn(ExerciseType::CLI());
        $exercise2->method('getType')->willReturn(ExerciseType::CLI());
        $exercise1->method('getName')->willReturn('Exercise 1');
        $exercise2->method('getName')->willReturn('Exercise 2');

        $oldData = [
            'current_exercise' => 'Exercise 3',
            'completed_exercises' => ['Exercise 1', 'Exercise 2'],
        ];

        $oldSave = $this->getTemporaryFile('.phpschool.json', json_encode($oldData));

        $serializer = new UserStateSerializer(
            $this->getTemporaryDirectory(),
            $this->workshopName,
            new ExerciseRepository([
                $exercise1,
                $exercise2
            ])
        );

        $state = $serializer->deSerialize();

        $this->assertEquals(['Exercise 1', 'Exercise 2'], $state->getCompletedExercises());
        $this->assertEquals('Exercise 3', $state->getCurrentExercise());

        $expected = [
            'My Workshop' => [
                'current_exercise' => 'Exercise 3',
                'completed_exercises' => ['Exercise 1', 'Exercise 2'],
            ],
        ];

        self::assertFileDoesNotExist($oldSave);
        $this->assertFileExists(Path::join($this->getTemporaryDirectory(), '.phpschool-save.json'));
        $this->assertEquals(
            $expected,
            json_decode(file_get_contents($this->getTemporaryFile('.phpschool-save.json')), true)
        );
    }

    public function testOldDataWillNotBeMigratedWhenNotInCorrectWorkshop(): void
    {
        $exercise1 = $this->createMock(CliExerciseInterface::class);
        $exercise2 = $this->createMock(CliExerciseInterface::class);
        $exercise1->method('getType')->willReturn(ExerciseType::CLI());
        $exercise2->method('getType')->willReturn(ExerciseType::CLI());
        $exercise1->method('getName')->willReturn('Exercise 1');
        $exercise2->method('getName')->willReturn('Exercise 2');

        $exercises = [
            $exercise1,
            $exercise2
        ];

        $repo = new ExerciseRepository($exercises);
        $oldData = [
            'current_exercise' => 'Exercise 3',
            'completed_exercises' => ['Exercise 1 from a diff workshop', 'Exercise 2 from a diff workshop'],
        ];

        $oldSave = $this->getTemporaryFile('.phpschool.json', json_encode($oldData));

        $serializer = new UserStateSerializer($this->getTemporaryDirectory(), $this->workshopName, $repo);
        $state = $serializer->deSerialize();

        $this->assertEquals([], $state->getCompletedExercises());
        $this->assertFalse($state->isAssignedExercise());

        $this->assertFileExists($oldSave);
        $this->assertEquals($oldData, json_decode(file_get_contents($oldSave), true));
        $this->assertFileDoesNotExist(Path::join($this->getTemporaryDirectory(), '.phpschool-save.json'));
    }

    public function testOldDataWillNotBeMigratedWhenNotInCorrectWorkshopWithOtherWorkshop(): void
    {
        $exercise1 = $this->createMock(CliExerciseInterface::class);
        $exercise2 = $this->createMock(CliExerciseInterface::class);
        $exercise1->method('getType')->willReturn(ExerciseType::CLI());
        $exercise2->method('getType')->willReturn(ExerciseType::CLI());
        $exercise1->method('getName')->willReturn('Exercise 1');
        $exercise2->method('getName')->willReturn('Exercise 2');

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

        $oldSave = $this->getTemporaryFile('.phpschool.json', json_encode($oldData));
        $newSave = $this->getTemporaryFile('.phpschool-save.json', json_encode($newData));

        $serializer = new UserStateSerializer(
            $this->getTemporaryDirectory(),
            $this->workshopName,
            new ExerciseRepository([
                $exercise1,
                $exercise2
            ])
        );
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
    }
}
