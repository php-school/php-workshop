<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\UserState;

use PhpSchool\PhpWorkshop\ExerciseRepository;

/**
 * Reads and persists the `UserState` instance to storage (file based).
 */
class LocalJsonSerializer implements Serializer
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $workshopName;

    /**
     * @var string
     */
    private const LEGACY_SAVE_FILE = '.phpschool.json';

    /**
     * @var string
     */
    private const SAVE_FILE = '.phpschool-save.json';

    /**
     * @var ExerciseRepository
     */
    private $exerciseRepository;

    /**
     * @param string $saveFileDirectory The path of the directory where the save file should be stored.
     * @param string $workshopName The name of the current workshop.
     * @param ExerciseRepository $exerciseRepository The repository of exercises.
     */
    public function __construct(
        string $saveFileDirectory,
        string $workshopName,
        ExerciseRepository $exerciseRepository,
    ) {
        $this->workshopName         = $workshopName;
        $this->path                 = $saveFileDirectory;
        $this->exerciseRepository   = $exerciseRepository;

        if (!file_exists($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    /**
     * Save the students state for this workshop to disk.
     *
     * @param UserState $state
     * @return void
     */
    public function serialize(UserState $state): void
    {
        $saveFile = sprintf('%s/%s', $this->path, self::SAVE_FILE);

        $data = file_exists($saveFile)
            ? $this->readJson($saveFile)
            : [];

        $data[$this->workshopName] = [
            'completed_exercises'   => $state->getCompletedExercises(),
            'current_exercise'      => $state->isAssignedExercise() ? $state->getCurrentExercise() : null,
        ];

        file_put_contents($saveFile, json_encode($data));
    }

    /**
     * Read a students state for this workshop from the disk.
     *
     * @return UserState
     */
    public function deSerialize(): UserState
    {
        $legacySaveFile = sprintf('%s/%s', $this->path, self::LEGACY_SAVE_FILE);
        if (file_exists($legacySaveFile)) {
            $userState = $this->migrateData($legacySaveFile);

            if ($userState instanceof UserState) {
                return $userState;
            }
        }

        $json = $this->readJson(sprintf('%s/%s', $this->path, self::SAVE_FILE));
        if (null === $json) {
            $this->wipeFile();
            return new UserState();
        }

        if (!isset($json[$this->workshopName]) || !is_array($json[$this->workshopName])) {
            return new UserState();
        }

        $json = $json[$this->workshopName];

        if (!array_key_exists('completed_exercises', $json)) {
            return new UserState();
        }

        if (!array_key_exists('current_exercise', $json)) {
            return new UserState();
        }

        if (!is_array($json['completed_exercises'])) {
            $json['completed_exercises'] = [];
        }

        foreach ($json['completed_exercises'] as $i => $exercise) {
            if (!is_string($exercise)) {
                unset($json['completed_exercises'][$i]);
            }
        }

        if (null !== $json['current_exercise'] && !is_string($json['current_exercise'])) {
            $json['current_exercise'] = null;
        }

        return new UserState(
            $json['completed_exercises'],
            $json['current_exercise'],
        );
    }

    /**
     * On early versions of the workshop the save data was not namespaced
     * and therefore it was impossible to have data for more than one workshop
     * at the same time. Therefore we must try to migrate that data in to the new namespaced
     * format in order to preserve users save data.
     *
     * We can only migrate data when using the workshop the data was originally saved from.
     *
     * @param string $legacySaveFile
     * @return null|UserState
     */
    private function migrateData(string $legacySaveFile): ?UserState
    {
        $data = $this->readJson($legacySaveFile);

        if (null === $data) {
            unlink($legacySaveFile);
            return null;
        }

        //lets check if the data is in the old format
        if (!isset($data['completed_exercises'], $data['current_exercise'])) {
            unlink($legacySaveFile);
            return null;
        }

        $completedExercises = $data['completed_exercises'];
        $availableExercises = $this->exerciseRepository->getAllNames();

        //check to see if this old data represents THIS workshop
        //if not we bail
        foreach ($completedExercises as $completedExercise) {
            if (!in_array($completedExercise, $availableExercises, true)) {
                return null;
            }
        }

        $userState = new UserState(
            $data['completed_exercises'],
            is_string($data['current_exercise']) ? $data['current_exercise'] : null,
        );

        $this->serialize($userState);

        unlink($legacySaveFile);
        return $userState;
    }

    /**
     * @param string $filePath
     * @return array<mixed>|null
     */
    private function readJson(string $filePath): ?array
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $data = (string) file_get_contents($filePath);

        if (trim($data) === "") {
            return null;
        }

        $data = @json_decode($data, true);

        if (null === $data && JSON_ERROR_NONE !== json_last_error()) {
            return null;
        }

        if (!is_array($data)) {
            return null;
        }

        return $data;
    }

    /**
     * Remove the file
     */
    private function wipeFile(): void
    {
        if (file_exists(sprintf('%s/%s', $this->path, self::SAVE_FILE))) {
            unlink(sprintf('%s/%s', $this->path, self::SAVE_FILE));
        }
    }
}
