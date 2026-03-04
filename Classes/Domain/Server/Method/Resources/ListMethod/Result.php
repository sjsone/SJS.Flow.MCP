<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method\Resources\ListMethod;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
class Result implements \JsonSerializable
{
    /**
     * @param array<mixed> $resources
     */
    public function __construct(
        public readonly array $resources,
        public readonly ?string $nextCursor,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        $data = [
            "resources" => $this->resources,
        ];

        if ($this->nextCursor) {
            $data['nextCursor'] = $this->nextCursor;
        }

        return $data;
    }
}
