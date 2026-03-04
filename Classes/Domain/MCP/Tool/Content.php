<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\MCP\Tool;

class Content implements \JsonSerializable
{
    /**
     * @var array<mixed>
     */
    protected array $content = [];

    /**
     * @var ?array<mixed>
     */
    protected ?array $structuredContent = null;

    public static function text(string $text): self
    {
        $content = new self();
        $content->addText($text);
        return $content;
    }

    /**
     * @param array<string,mixed> $structuredContent
     */
    public static function structured(array $structuredContent): self
    {
        $content = new self();
        $content->setStructuredContent($structuredContent);
        return $content;
    }

    /**
     * @param array<string,mixed> $structuredContent
     */
    public static function structuredWithFallback(array $structuredContent): self
    {
        $fallbackJson = json_encode($structuredContent);
        if ($fallbackJson === false) {
            throw new \InvalidArgumentException("structured content must be encodable to JSON");
        }
        return self::structured($structuredContent)->addText($fallbackJson);
    }

    public function __construct()
    {
    }

    public function addText(string $text): static
    {
        $this->content[] = [
            "type" => "text",
            "text" => $text
        ];

        return $this;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function setStructuredContent(array $data): void
    {
        $this->structuredContent = $data;
    }

    public function jsonSerialize(): mixed
    {
        $data = [];

        if (!empty($this->content)) {
            $data["content"] = $this->content;
        }

        if ($this->structuredContent !== null) {
            $structuredContent = $this->structuredContent;
            if (empty($structuredContent)) {
                $structuredContent = new \stdClass();
            }
            $data["structuredContent"] = $structuredContent;
        }

        return $data;
    }
}
