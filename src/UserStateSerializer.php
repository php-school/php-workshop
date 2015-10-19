<?php

namespace PhpSchool\PhpWorkshop;

/**
 * Class UserStateSerializer
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class UserStateSerializer
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;

        if (file_exists($path)) {
            return;
        }

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
    }

    /**
     * @param UserState $state
     *
     * @return int
     */
    public function serialize(UserState $state)
    {
        return file_put_contents($this->path, json_encode([
            'completed_exercises'   => $state->getCompletedExercises(),
            'current_exercise'      => $state->getCurrentExercise(),
        ]));
    }

    /**
     * @return UserState
     */
    public function deSerialize()
    {
        if (!file_exists($this->path)) {
            return new UserState();
        }

        $data = file_get_contents($this->path);

        if (trim($data) === "") {
            $this->wipeFile();
            return new UserState();
        }

        $json = @json_decode($data, true);

        if (null === $json && JSON_ERROR_NONE !== json_last_error()) {
            $this->wipeFile();
            return new UserState();

        }

        if (!array_key_exists('completed_exercises', $json)) {
            $this->wipeFile();
            return new UserState();
        }

        if (!is_array($json['completed_exercises'])) {
            $this->wipeFile();
            return new UserState();
        }

        foreach ($json['completed_exercises'] as $exercise) {
            if (!is_string($exercise)) {
                $this->wipeFile();
                return new UserState();
            }
        }

        if (!array_key_exists('current_exercise', $json)) {
            $this->wipeFile();
            return new UserState();
        }

        if (null !== $json['current_exercise'] && !is_string($json['current_exercise'])) {
            $this->wipeFile();
            return new UserState();
        }

        return new UserState(
            $json['completed_exercises'],
            $json['current_exercise']
        );
    }

    /**
     * Remove the file
     */
    private function wipeFile()
    {
        unlink($this->path);
    }
}
