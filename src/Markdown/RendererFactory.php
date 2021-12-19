<?php

namespace PhpSchool\PhpWorkshop\Markdown;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use League\CommonMark\Environment\Environment as EnvironmentV3;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Parser\MarkdownParser;
use PhpSchool\PhpWorkshop\Factory\MarkdownCliRendererFactory;
use PhpSchool\PhpWorkshop\Factory\MarkdownCliRendererV3Factory;
use Psr\Container\ContainerInterface;

class RendererFactory
{
    public function __invoke(ContainerInterface $c) : Renderer
    {
        if ($this->isV3()) {
            $parser = new MarkdownParser((new EnvironmentV3())->addExtension(new CommonMarkCoreExtension()));
            $renderer = (new MarkdownCliRendererV3Factory)->__invoke($c);

            return new LeagueCommonMarkV2Renderer($parser, $renderer);
        }

        return new LeagueCommonMarkRenderer(
            new DocParser(Environment::createCommonMarkEnvironment()),
            (new MarkdownCliRendererFactory)->__invoke($c)
        );
    }

    private function isV3(): bool
    {
        return InstalledVersions::satisfies(new VersionParser(), 'aydin-hassan/cli-md-renderer', '^3.0|dev-master');
    }
}