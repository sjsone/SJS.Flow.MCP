<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\JsonSchema;

class IntegerSchema extends AbstractSchema
{
    protected string $type = 'integer';

    public function __construct(
        ?string $description = null,
        mixed $default = null,
        protected ?int $minimum = null,
        protected ?int $maximum = null
    ) {
        parent::__construct($description, $default);
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        if ($this->minimum !== null) {
            $data['minimum'] = $this->minimum;
        }
        if ($this->maximum !== null) {
            $data['maximum'] = $this->maximum;
        }
        return $data;
    }
}
