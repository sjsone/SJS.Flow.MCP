<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\FeatureSet\OptionDefinedFeatureSet;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManager;
use SJS\Flow\MCP\FeatureSet\AbstractFeatureSet;
use SJS\Flow\MCP\Domain\MCP\Tool;
use SJS\Flow\MCP\JsonSchema\SchemaFactory;

class OptionDefinedTool extends Tool
{
    #[Flow\Inject]
    protected ObjectManager $objectManager;

    public function __construct(
        protected readonly string $implementation,
        protected readonly string $method,
        string $name,
        string $description,
        \SJS\Flow\MCP\JsonSchema\AbstractSchema $inputSchema,
        \SJS\Flow\MCP\JsonSchema\AbstractSchema|null $outputSchema = null,
        Tool\Annotations|null $annotations = null,
    ) {
        parent::__construct(
            name: $name,
            description: $description,
            inputSchema: $inputSchema,
            outputSchema: $outputSchema,
            annotations: $annotations
        );
    }

    /**
     * @param array<string,mixed> $input
     */
    public function run(\Neos\Flow\Mvc\ActionRequest $actionRequest, array $input): Tool\Content
    {
        $instance = $this->objectManager->get($this->implementation);

        $method = $this->method;

        /** @var null|string|array<string,mixed> */
        $result = $instance->$method($actionRequest, $input);

        if (\is_array($result) && \array_is_list($result)) {
            return Tool\Content::structuredWithFallback($result);
        }

        if (\is_string($result)) {
            return Tool\Content::text($result);
        }

        return Tool\Content::text("");
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(string $name, array $data): self
    {
        $callback = $data["callback"] ?? null;
        if (!\is_string($callback)) {
            throw new \InvalidArgumentException("Tool '$name' callback must be a string");
        }
        [$implementation, $method] = explode("::", $callback, 2);

        $description = $data["description"] ?? "";
        if (!\is_string($description)) {
            throw new \InvalidArgumentException("Tool '$name' description must be a string");
        }

        $inputSchema = $data["inputSchema"] ?? null;
        if (!\is_array($inputSchema)) {
            throw new \InvalidArgumentException("Tool '$name' inputSchema must be an array");
        }
        /** @var array<string, mixed> $inputSchema */

        $outputSchema = $data["outputSchema"] ?? null;
        if ($outputSchema !== null && !\is_array($outputSchema)) {
            throw new \InvalidArgumentException("Tool '$name' outputSchema must be an array or null");
        }
        /** @var array<string, mixed>|null $outputSchema */

        return new self(
            $implementation,
            $method,
            $name,
            $description,
            SchemaFactory::buildFromArray($inputSchema),
            $outputSchema !== null ? SchemaFactory::buildFromArray($outputSchema) : null,
            null,
        );
    }
}