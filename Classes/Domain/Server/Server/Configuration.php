<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Server;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Domain\Server\Server\Configuration\FeatureSet as FeatureSetConfiguration;

#[Flow\Proxy(false)]
class Configuration
{
    /**
     * @param array<mixed> $capabilities
     * @param array<FeatureSetConfiguration> $featureSets
     */
    public function __construct(
        public readonly array $capabilities,
        public readonly array $featureSets,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $featureSetsConfiguration = $data["featureSets"] ?? [];
        if (!\is_array($featureSetsConfiguration)) {
            throw new \InvalidArgumentException("featureSets has to be an array");
        }

        $featureSets = [];
        foreach ($featureSetsConfiguration as $name => $configuration) {
            if (!\is_string($name)) {
                throw new \InvalidArgumentException("featureSets has to be an associative array.");
            }
            if (!\is_array($configuration) && !\is_string($configuration)) {
                throw new \InvalidArgumentException("featureSets values have to be either a FQCN or an configuration object.");

            }
            /** @var array<string, mixed> $configuration */
            $featureSets[$name] = FeatureSetConfiguration::fromNameAndMixed($name, $configuration);
        }

        $capabilities = $data["capabilities"] ?? [];
        if (!\is_array($capabilities)) {
            throw new \InvalidArgumentException("capabilities has to be an array");
        }

        return new self(
            capabilities: $capabilities,
            featureSets: $featureSets,
        );
    }
}