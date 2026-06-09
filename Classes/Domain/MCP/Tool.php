<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\MCP;

use SJS\Flow\MCP\Domain\Connection\ServerContext;
use SJS\Flow\MCP\Domain\MCP\Tool\Annotations;
use SJS\Flow\MCP\FeatureSet\FeatureSetInterface;
use SJS\Flow\MCP\JsonSchema\AbstractSchema;
use SJS\Flow\MCP\Domain\MCP\Tool\Content as ToolContent;

abstract class Tool implements \JsonSerializable
{
    public ?string $prefix = null;

    public function nameWithPrefix(): string
    {
        return ($this->prefix !== null ? "{$this->prefix}_" : "") . $this->name;
    }

    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly AbstractSchema $inputSchema,
        public readonly ?AbstractSchema $outputSchema = null,
        public readonly ?Annotations $annotations = null,
        public readonly ?FeatureSetInterface $featureSet = null,
    ) {
    }

    /**
     * @param array<string,mixed> $input
     * @return array<string,mixed>
     */
    public function initializeInput(mixed $input): array
    {
        return $input;
    }

    /**
     * @param array<string,mixed> $input
     */
    abstract public function run(ServerContext $serverContext, array $input): ToolContent;

    public function jsonSerialize(): mixed
    {

        $data = [
            'name' => $this->nameWithPrefix(),
            'description' => $this->description,
            'inputSchema' => $this->inputSchema,
        ];

        if ($this->outputSchema) {
            $data['outputSchema'] = $this->outputSchema;
        }

        if ($this->annotations) {
            $data['annotations'] = $this->annotations;
        }

        return $data;
    }
}
