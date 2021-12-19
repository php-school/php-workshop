<?php

namespace PhpSchool\PhpWorkshop\Markdown;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use League\CommonMark\Environment\Environment as EnvironmentV3;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Parser\MarkdownParser;
use Psr\Container\ContainerInterface;

class ParserFactory
{
    public function __invoke(ContainerInterface $c): object
    {
        return $this->isV2()
            ? new MarkdownParser((new EnvironmentV3())->addExtension(new CommonMarkCoreExtension()))
            : new DocParser(Environment::createCommonMarkEnvironment());
    }

    private function isV2(): bool
    {
        var_dump(InstalledVersions::satisfies(new VersionParser(), 'league/commonmark', '^2.0'));
        return InstalledVersions::satisfies(new VersionParser(), 'league/commonmark', '^2.0');
    }
}