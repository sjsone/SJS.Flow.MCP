<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\JsonSchema;

class AnySchema extends AbstractSchema
{
    protected string $type = '';

    /**
     * @param array<AbstractSchema> $options
     */
    public function __construct(
        ?string $description = null,
        mixed $default = null,
        /** @property array<AbstractSchema> $options */
        protected array $options = [],
    ) {
        parent::__construct($description, $default);
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        unset($data["type"]);

        $data["anyOf"] = $this->options;
        return $data;
    }
}
