<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\FeatureSet;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\FeatureSet\AbstractFeatureSet;
use SJS\Flow\MCP\FeatureSet\OptionDefinedFeatureSet\OptionDefinedTool;

#[Flow\Scope("singleton")]
class OptionDefinedFeatureSet extends AbstractFeatureSet
{
    public function initialize(): void
    {
        $toolsConfiguration = $this->options["tools"] ?? [];
        if (!\is_array($toolsConfiguration)) {
            throw new \InvalidArgumentException("options['tools'] must be an array");
        }

        foreach ($toolsConfiguration as $name => $configuration) {
            if (!\is_string($name)) {
                throw new \InvalidArgumentException("Tool name must be a string");
            }
            if (!\is_array($configuration)) {
                throw new \InvalidArgumentException("Tool configuration for '$name' must be an array");
            }
            /** @var array<string, mixed> $configuration */

            $toolInstance = OptionDefinedTool::fromArray($name, $configuration);
            $toolInstance->prefix = $this->generateToolCallPrefix();

            $this->tools[$toolInstance->nameWithPrefix()] = $toolInstance;
        }
    }
}
