<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

/**
 * A failure result representing the situation where there were composer package requirements
 * and they were not met.
 */
class ComposerFailure implements FailureInterface
{
    use ResultTrait;

    /**
     * @var array<string>
     */
    private $missingPackages;

    /**
     * @var string|null
     */
    private $missingComponent;

    /**
     * @var array<string>
     */
    private static $validComponents = ['composer.json', 'composer.lock', 'vendor'];

    /**
     * @param CheckInterface $check The check that produced this result.
     * @param array<string> $missingPackages
     */
    public function __construct(CheckInterface $check, string $missingComponent = null, array $missingPackages = [])
    {
        $this->check = $check;
        $this->missingPackages = $missingPackages;

        if ($missingComponent !== null && !in_array($missingComponent, self::$validComponents, true)) {
            throw InvalidArgumentException::notValidParameter(
                'missingComponent',
                self::$validComponents,
                $missingComponent
            );
        }
        $this->missingComponent = $missingComponent;
    }

    /**
     * @param array<string> $missingPackages
     */
    public static function fromCheckAndMissingPackages(CheckInterface $check, array $missingPackages): self
    {
        return new self($check, null, $missingPackages);
    }

    public static function fromCheckAndMissingFileOrFolder(CheckInterface $check, string $missingComponent): self
    {
        return new self($check, $missingComponent);
    }

    public function isMissingPackages(): bool
    {
        return count($this->missingPackages) > 0;
    }

    /**
     * @return array<string>
     */
    public function getMissingPackages(): array
    {
        return $this->missingPackages;
    }

    public function isMissingComponent(): bool
    {
        return $this->missingComponent !== null;
    }

    public function getMissingComponent(): ?string
    {
        return $this->missingComponent;
    }

    /**
     * @return array{
     *     is_missing_component: bool,
     *     is_missing_packages: bool,
     *     missing_component: ?string,
     *     missing_packages: array<string>
     * }
     */
    public function toArray(): array
    {
        return [
            'is_missing_component' => $this->isMissingComponent(),
            'is_missing_packages' => $this->isMissingPackages(),
            'missing_component' => $this->getMissingComponent(),
            'missing_packages' => $this->getMissingPackages()
        ];
    }
}
