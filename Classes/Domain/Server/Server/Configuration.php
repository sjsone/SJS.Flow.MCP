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
     * @param list<string> $disabledTools tool names including their prefix, e.g. "workspace_delete_workspace"
     */
    public function __construct(
        public readonly array $capabilities,
        public readonly array $featureSets,
        public readonly array $disabledTools = [],
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
            if ($configuration === null) {
                // unset via Settings (`name: ~`), e.g. to switch off a feature set another package registers
                continue;
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

        $disabledTools = $data["disabledTools"] ?? [];
        if (!\is_array($disabledTools)) {
            throw new \InvalidArgumentException("disabledTools has to be a list of tool names");
        }
        $disabledTools = \array_values(\array_filter(
            $disabledTools,
            // allows removing an entry again in a later Settings file by setting it to ~
            fn(mixed $toolName) => $toolName !== null
        ));
        foreach ($disabledTools as $toolName) {
            if (!\is_string($toolName)) {
                throw new \InvalidArgumentException("disabledTools has to be a list of tool names");
            }
        }
        /** @var list<string> $disabledTools */

        return new self(
            capabilities: $capabilities,
            featureSets: $featureSets,
            disabledTools: $disabledTools,
        );
    }
}