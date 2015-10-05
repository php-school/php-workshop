<?php

namespace PhpWorkshop\PhpWorkshop;

/**
 * Class UserStateSerializer
 * @package PhpWorkshop\PhpWorkshop
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
            mkdir(dirname($path), 0777);
        }

        touch($path);
    }

    /**
     * @param UserState $state
     */
    public function serialize(UserState $state)
    {
        file_put_contents($this->path, json_encode([
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

        if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
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
