<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Server\Configuration;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
class FeatureSet
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public readonly string $name,
        public readonly string $implementation,
        public readonly array $options,
    ) {
    }

    /**
     * @param string $name
     * @param string|array<string,mixed> $data
     */
    public static function fromNameAndMixed(string $name, mixed $data): self
    {
        if (\is_string($data)) {
            $implementation = $data;
            $options = [];
        } else if (\is_array($data)) {
            $implementation = $data["implementation"];
            $options = $data["options"] ?? [];
        } else {
            throw new \InvalidArgumentException("FeatureSet $name configuration has to be either a string or an array.");
        }

        if (!\is_string($implementation)) {
            throw new \InvalidArgumentException("FeatureSet $name implementation must be a string");
        }
        if (!class_exists($implementation)) {
            throw new \InvalidArgumentException("FeatureSet $name implementation has to be an existing class.\n'$implementation' does not exist");
        }
        if (!\is_array($options)) {
            throw new \InvalidArgumentException("FeatureSet $name options must be an array");
        }
        /** @var array<string, mixed> $options */

        return new self(
            name: $name,
            implementation: $implementation,
            options: $options,
        );
    }
}