<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\JsonSchema;

class SchemaFactory
{

    /**
     * @param array<string,mixed> $data
     */
    public static function buildFromArray(array $data): AbstractSchema
    {
        if (isset($data["anyOf"]) && \is_array($data["anyOf"])) {
            return self::buildAnySchema($data);
        }

        if (!isset($data["type"])) {
            throw new \InvalidArgumentException("Schema must have either 'type' or 'anyOf' field");
        }

        if (!\is_string($data["type"])) {
            throw new \InvalidArgumentException("Schema 'type' must be a string");
        }

        $type = $data["type"];

        return match ($type) {
            "boolean" => self::buildBooleanSchema($data),
            "string" => self::buildStringSchema($data),
            "integer" => self::buildIntegerSchema($data),
            "number" => self::buildNumberSchema($data),
            "array" => self::buildArraySchema($data),
            "object" => self::buildObjectSchema($data),
            default => throw new \InvalidArgumentException("Unsupported schema type: {$type}")
        };
    }

    /**
     * @param array<string,mixed> $data
     */
    private static function buildAnySchema(array $data): AnySchema
    {
        /** @var array<string, mixed> $anyOfData */
        $anyOfData = $data["anyOf"];
        if (!\is_array($anyOfData)) {
            throw new \InvalidArgumentException("anyOf must be an array");
        }

        $options = [];
        foreach ($anyOfData as $optionData) {
            if (\is_array($optionData)) {
                /** @var array<string, mixed> $optionData */
                $options[] = self::buildFromArray($optionData);
            }
        }

        return new AnySchema(
            description: self::extractString($data, "description"),
            default: $data["default"] ?? null,
            options: $options
        );
    }

    /**
     * @param array<string,mixed> $data
     */
    private static function buildBooleanSchema(array $data): BooleanSchema
    {
        return new BooleanSchema(
            description: self::extractString($data, "description"),
            default: $data["default"] ?? null
        );
    }

    /**
     * @param array<string,mixed> $data
     */
    private static function buildStringSchema(array $data): StringSchema
    {
        return new StringSchema(
            description: self::extractString($data, "description"),
            default: $data["default"] ?? null,
            minLength: self::extractIntOrNull($data, "minLength"),
            maxLength: self::extractIntOrNull($data, "maxLength"),
            pattern: self::extractString($data, "pattern"),
            format: self::extractString($data, "format"),
            enum: self::extractArrayOrNull($data, "enum")
        );
    }

    /**
     * @param array<string,mixed> $data
     */
    private static function buildIntegerSchema(array $data): IntegerSchema
    {
        return new IntegerSchema(
            description: self::extractString($data, "description"),
            default: $data["default"] ?? null,
            minimum: self::extractIntOrNull($data, "minimum"),
            maximum: self::extractIntOrNull($data, "maximum")
        );
    }

    /**
     * @param array<string,mixed> $data
     */
    private static function buildNumberSchema(array $data): NumberSchema
    {
        return new NumberSchema(
            description: self::extractString($data, "description"),
            default: $data["default"] ?? null,
            minimum: self::extractFloatOrNull($data, "minimum"),
            maximum: self::extractFloatOrNull($data, "maximum")
        );
    }

    /**
     * @param array<string,mixed> $data
     */
    private static function buildArraySchema(array $data): ArraySchema
    {
        $items = null;
        if (isset($data["items"])) {
            if (!\is_array($data["items"])) {
                throw new \InvalidArgumentException("Array 'items' must be an array or object");
            }
            /** @var array<string, mixed> $itemsData */
            $itemsData = $data["items"];
            $items = self::buildFromArray($itemsData);
        }

        return new ArraySchema(
            description: self::extractString($data, "description"),
            default: $data["default"] ?? null,
            items: $items
        );
    }

    /**
     * @param array<string,mixed> $data
     * @return ObjectSchema
     */
    private static function buildObjectSchema(array $data): ObjectSchema
    {
        /** @var array<string, AbstractSchema> $properties */
        $properties = [];
        if (isset($data["properties"])) {
            if (!\is_array($data["properties"])) {
                throw new \InvalidArgumentException("Object 'properties' must be an array");
            }
            foreach ($data["properties"] as $name => $propertyData) {
                if (\is_array($propertyData) && \is_string($name)) {
                    /** @var array<string, mixed> $propertyData */
                    $properties[$name] = self::buildFromArray($propertyData);
                }
            }
        }

        /** @var array<string> $required */
        $required = [];
        if (isset($data["required"])) {
            if (!\is_array($data["required"])) {
                throw new \InvalidArgumentException("Object 'required' must be an array");
            }
            foreach ($data["required"] as $requiredField) {
                if (\is_string($requiredField)) {
                    $required[] = $requiredField;
                }
            }
        }

        return new ObjectSchema(
            title: self::extractString($data, "title"),
            description: self::extractString($data, "description"),
            properties: $properties,
            required: $required
        );
    }

    /**
     * @param array<string,mixed> $data
     */
    private static function extractString(array $data, string $key): ?string
    {
        if (!isset($data[$key])) {
            return null;
        }

        if (!\is_string($data[$key])) {
            throw new \InvalidArgumentException("Schema '{$key}' must be a string or null, " . \get_debug_type($data[$key]) . " given");
        }

        return $data[$key];
    }

    /**
     * @param array<string,mixed> $data
     */
    private static function extractIntOrNull(array $data, string $key): ?int
    {
        if (!isset($data[$key])) {
            return null;
        }

        if (!\is_int($data[$key])) {
            throw new \InvalidArgumentException("Schema '{$key}' must be an integer or null, " . \get_debug_type($data[$key]) . " given");
        }

        return $data[$key];
    }

    /**
     * @param array<string,mixed> $data
     */
    private static function extractFloatOrNull(array $data, string $key): ?float
    {
        if (!isset($data[$key])) {
            return null;
        }

        if (!\is_numeric($data[$key])) {
            throw new \InvalidArgumentException("Schema '{$key}' must be numeric or null, " . \get_debug_type($data[$key]) . " given");
        }

        return (float) $data[$key];
    }

    /**
     * @param array<string,mixed> $data
     * @return array<mixed>|null
     */
    private static function extractArrayOrNull(array $data, string $key): ?array
    {
        if (!isset($data[$key])) {
            return null;
        }

        if (!\is_array($data[$key])) {
            throw new \InvalidArgumentException("Schema '{$key}' must be an array or null, " . \get_debug_type($data[$key]) . " given");
        }

        return $data[$key];
    }
}