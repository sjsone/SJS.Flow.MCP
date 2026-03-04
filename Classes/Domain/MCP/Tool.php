<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\MCP;

use Neos\Flow\Mvc\ActionRequest;
use SJS\Flow\MCP\Domain\MCP\Tool\Annotations;
use SJS\Flow\MCP\JsonSchema\AbstractSchema;

abstract class Tool implements \JsonSerializable
{
    public ?string $prefix = null;

    // TODO: use get hook instead of method
    public function nameWithPrefix(): string
    {
        return ($this->prefix !== null ? "{$this->prefix}_" : "") . $this->name;
    }

    // TODO: improve DX for create new Tools because using parent::__construct is a bit awkward

    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly AbstractSchema $inputSchema,
        public readonly ?AbstractSchema $outputSchema = null,
        public readonly ?Annotations $annotations = null,
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
    abstract public function run(ActionRequest $actionRequest, array $input): mixed;

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
