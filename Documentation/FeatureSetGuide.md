# MCP FeatureSet Package Guide

How to structure FeatureSet packages, implement FeatureSets, and write Tools
for the SJS.Flow.MCP framework.

---

## Package Structure

```
Vendor.Site.FeatureSet.Example/
├── Documentation/                   # Agent-facing docs (optional)
├── Classes/
│   └── FooFeatureSet.php            # The FeatureSet class
│   └── FooFeatureSet/
│       ├── DoThingTool.php           # A Tool
│       ├── DoOtherThingTool.php      # Another Tool
│       └── AbstractFooTool.php       # Shared base class for this FeatureSet's tools (optional)
├── Configuration/
│   └── Settings.Server.yaml          # Wiring: register FeatureSets on the MCP server
├── composer.json
└── README.md
```

### composer.json

```json
{
    "name": "vendor/feature-set-example",
    "type": "neos-package",
    "description": "MCP FeatureSet for Example",
    "require": {
        "neos/neos": "^9.0",
        "sjs/flow-mcp": "^1.0",
        "sjs/neos-mcp": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "Vendor\\Site\\FeatureSet\\Example\\": "Classes"
        }
    }
}
```

- `type` is always `neos-package`.
- Require `sjs/flow-mcp` (the framework) and optionally `sjs/neos-mcp` (if you
  use Neos CMS dependencies like the Content Repository, site detection, or
  workspace services).

---

## FeatureSet Class

A FeatureSet groups related Tools under a namespace prefix. It extends
`AbstractFeatureSet` and registers Tools in its `initialize()` method.

### Minimal FeatureSet

```php
<?php
declare(strict_types=1);

namespace Vendor\Site\FeatureSet\Example;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\FeatureSet\AbstractFeatureSet;
use Vendor\Site\FeatureSet\Example\FooFeatureSet\DoThingTool;
use Vendor\Site\FeatureSet\Example\FooFeatureSet\DoOtherThingTool;

#[Flow\Scope("singleton")]
class FooFeatureSet extends AbstractFeatureSet
{
    public function initialize(): void
    {
        $this->addTool(DoThingTool::class);
        $this->addTool(DoOtherThingTool::class);
    }
}
```

### Key rules

| Rule | Why |
|------|-----|
| `#[Flow\Scope("singleton")]` | FeatureSets are stateless services; singleton avoids repeated initialization |
| One FeatureSet per domain concern | `WorkspaceFeatureSet`, `ContentFeatureSet`, `NodeTypeFeatureSet` — each with a clear prefix |
| Tool classes live in a subdirectory named after the FeatureSet | `FooFeatureSet/DoThingTool.php` keeps the package navigable |
| `addTool()` takes a `class-string<Tool>` | The class must exist and implement `ToolConstructor` |

### Tool call prefix

The prefix is auto-derived from the FeatureSet class name by stripping the
`FeatureSet` suffix and lowercasing:

| FeatureSet class | Prefix | Tool names |
|-----------------|--------|------------|
| `WorkspaceFeatureSet` | `workspace` | `workspace_list_workspaces`, `workspace_create_workspace`, ... |
| `DimensionFeatureSet` | `dimension` | `dimension_list_dimensions`, ... |
| `FlowFeatureSet` | `flow` | `flow_list_packages`, ... |

If you need to control the prefix manually, set `$this->toolCallPrefix` or
`$this->useToolCallPrefix = false` before calling `addTool()`.

### Overriding `toolsCall()` for exception handling

If your tools wrap Content Repository operations, override `toolsCall()` to
catch exceptions:

```php
public function toolsCall(string $toolName, array $arguments): Content
{
    return $this->catchCRExceptions(fn() => parent::toolsCall($toolName, $arguments));
}
```

`catchCRExceptions()` (provided by `AbstractFeatureSet`) catches `\Exception`,
logs the error via the MCP logger, and returns a text Content with the error
message — so raw exceptions never reach the MCP client.

---

## Tool Classes

A Tool is a class that extends `Tool` and implements `ToolConstructor`.

### Minimal Tool

```php
<?php
declare(strict_types=1);

namespace Vendor\Site\FeatureSet\Example\FooFeatureSet;

use SJS\Flow\MCP\Domain\Connection\ServerContext;
use SJS\Flow\MCP\Domain\MCP\Tool;
use SJS\Flow\MCP\Domain\MCP\Tool\Annotations;
use SJS\Flow\MCP\Domain\MCP\Tool\Content;
use SJS\Flow\MCP\Domain\MCP\ToolConstructor;
use SJS\Flow\MCP\FeatureSet\FeatureSetInterface;
use SJS\Flow\MCP\JsonSchema\ObjectSchema;
use SJS\Flow\MCP\JsonSchema\StringSchema;

class DoThingTool extends Tool implements ToolConstructor
{
    public function __construct(FeatureSetInterface $featureSet)
    {
        parent::__construct(
            name: 'do_thing',
            description: 'Does a thing.',
            inputSchema: new ObjectSchema(properties: [
                'some_param' => (new StringSchema(
                    description: "A required parameter"
                ))->required(),
                'optional_param' => new StringSchema(
                    description: "An optional parameter"
                ),
            ]),
            annotations: new Annotations(
                title: 'Do Thing',
                readOnlyHint: true,        // set ONE of these
                // destructiveHint: true,   // for write operations
                // idempotentHint: true,    // for idempotent writes
                // openWorldHint: true,     // for external interactions
            ),
            featureSet: $featureSet,
        );
    }

    /**
     * @param array<string,mixed> $input
     */
    public function run(ServerContext $serverContext, array $input): Content
    {
        $param = $input['some_param'];

        // ... do the work ...

        return Content::structuredWithFallback([
            'status' => 'success',
            'result' => $param,
        ]);
    }
}
```

### Tool constructor parameters

| Parameter | Type | Required | Notes |
|-----------|------|----------|-------|
| `name` | `string` | Yes | Snake_case, no prefix (the FeatureSet adds it) |
| `description` | `string` | Yes | Shown to AI agents; be precise about what the tool does |
| `inputSchema` | `AbstractSchema` | Yes | Use `ObjectSchema` for structured input, `AnySchema` for no input |
| `outputSchema` | `?AbstractSchema` | No | Describe the output shape if helpful |
| `annotations` | `?Annotations` | No | Hints for the MCP client (readOnlyHint, destructiveHint, etc.) |
| `featureSet` | `FeatureSetInterface` | Yes | Pass through from constructor |

### Annotations

Exactly **one** operation-type hint should be set (all are optional, but at
least one of `readOnlyHint`/`destructiveHint` should be truthy):

| Property | Meaning |
|----------|---------|
| `readOnlyHint: true` | Tool does not modify state |
| `destructiveHint: true` | Tool may delete or irreversibly change data |
| `idempotentHint: true` | Repeated calls with same args have no additional effect |
| `openWorldHint: true` | Tool interacts with external systems |

### Content return types

| Method | Use case |
|--------|----------|
| `Content::text(string)` | Plain text message (errors, simple confirmations) |
| `Content::structured(array)` | Structured data for programmatic consumption |
| `Content::structuredWithFallback(array)` | Structured data + JSON fallback text (preferred for structured output) |

**Prefer `structuredWithFallback()`** for all non-trivial output — it gives AI
clients typed data and plain-text clients a JSON string.

### Input validation

`AbstractFeatureSet.toolsCall()` validates required arguments before invoking
`run()` — if a required property is missing or null, `\InvalidArgumentException`
is thrown and converted to a text Content. You don't need to repeat that
validation in `run()`.

---

## Shared Tool Patterns

When multiple tools in a FeatureSet share logic, extract it into a shared
base class or a trait.

### Abstract base tool

```php
<?php
declare(strict_types=1);

namespace Vendor\Site\FeatureSet\Example\FooFeatureSet;

use Neos\Flow\Annotations as Flow;
use Psr\Log\LoggerInterface;
use SJS\Flow\MCP\Domain\Connection\ServerContext;
use SJS\Flow\MCP\Domain\MCP\Tool;
use SJS\Flow\MCP\Domain\MCP\Tool\Content;
use SJS\Flow\MCP\Domain\MCP\ToolConstructor;
use Neos\Neos\FrontendRouting\SiteDetection\SiteDetectionResult;

abstract class AbstractFooTool extends Tool implements ToolConstructor
{
    #[Flow\Inject(name: "SJS.Flow.MCP:MCPLogger", lazy: false)]
    protected LoggerInterface $logger;

    protected function getSiteDetectionResult(ServerContext $serverContext): SiteDetectionResult
    {
        return SiteDetectionResult::fromRequest(
            $serverContext->request->getHttpRequest()
        );
    }

    /**
     * @param array<string,mixed> $input
     */
    protected function retrieveRequiredString(array $input, string $key): string
    {
        return $input[$key];
    }

    protected function runSafely(\Closure $fn): Content
    {
        try {
            /** @var Content $result */
            $result = $fn();
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Foo operation failed', ['exception' => $e]);
            return Content::text($e->getMessage());
        }
    }
}
```

Concrete tools then extend `AbstractFooTool` instead of `Tool` directly:

```php
class DoThingTool extends AbstractFooTool
{
    // ...
}
```

### Trait

Use a trait when the shared logic is also needed across different FeatureSet
hierarchies:

```php
trait ContentRepositoryTool
{
    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    protected function getContentRepository(ServerContext $serverContext): ContentRepository
    {
        $httpRequest = $serverContext->request->getHttpRequest();
        $contentRepositoryId = SiteDetectionResult::fromRequest($httpRequest)
            ->contentRepositoryId;
        return $this->contentRepositoryRegistry->get($contentRepositoryId);
    }
}
```

Traits can carry their own `#[Flow\Inject]` properties.

### When to use which

| Pattern | Use when |
|---------|----------|
| Abstract base tool (extends `Tool`) | Shared logic specific to one FeatureSet's tool family |
| Trait | Shared logic usable across multiple FeatureSets or tool hierarchies |

---

## Configuration Wiring

Register FeatureSets on the MCP server in `Configuration/Settings.Server.yaml`:

```yaml
SJS:
  Flow:
    MCP:
      server:
        mcp:
          featureSets:
            # <prefix>: <FeatureSet FQCN>
            foo: \Vendor\Site\FeatureSet\Example\FooFeatureSet
```

The key is the server name (`mcp` is the default). Under `featureSets`, map
each prefix to its FeatureSet FQCN. The prefix determines the tool namespace;
if it doesn't match the FeatureSet's auto-derived prefix, the configuration
key takes precedence.

### Semantic configuration filenames

Follow Neos coding guidelines — if you need to configure other packages within
your FeatureSet package, use semantic filenames:

```
Configuration/
├── Settings.Server.yaml             # Wiring for SJS.Flow.MCP.server
├── Settings.SJS.Flow.MCP.yaml       # Overrides for the MCP framework itself
├── Settings.Neos.Neos.yaml          # Overrides for Neos CMS
├── Objects.yaml                     # Object configuration
└── Routes.yaml                      # Custom routes
```

---

## When to Use `OptionDefinedFeatureSet`

`OptionDefinedFeatureSet` lets you define Tools purely via YAML configuration
(in `options.tools`) instead of writing PHP classes. Use it for simple
passthrough tools that delegate to an existing class+method:

```yaml
SJS:
  Flow:
    MCP:
      server:
        mcp:
          featureSets:
            myTools:
              className: \SJS\Flow\MCP\FeatureSet\OptionDefinedFeatureSet
              options:
                tools:
                  my_tool:
                    callback: \Vendor\Site\Service\SomeService::someMethod
                    description: "Does something"
                    inputSchema:
                      type: object
                      properties:
                        param1:
                          type: string
                          description: "A parameter"
                      required: [param1]
```

The callback method receives `(ServerContext $serverContext, array $input)` and
can return a `string` (becomes text Content) or an `array` (becomes structured
Content).

**Prefer writing Tool classes** for anything non-trivial — `OptionDefinedFeatureSet`
is best for quick prototypes or one-line delegations.

---

## Complete Example: A Read-Only Greeting FeatureSet

### `Classes/GreetingFeatureSet.php`

```php
<?php
declare(strict_types=1);

namespace Vendor\Site\FeatureSet\Example;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\FeatureSet\AbstractFeatureSet;
use Vendor\Site\FeatureSet\Example\GreetingFeatureSet\HelloTool;

#[Flow\Scope("singleton")]
class GreetingFeatureSet extends AbstractFeatureSet
{
    public function initialize(): void
    {
        $this->addTool(HelloTool::class);
    }
}
```

### `Classes/GreetingFeatureSet/HelloTool.php`

```php
<?php
declare(strict_types=1);

namespace Vendor\Site\FeatureSet\Example\GreetingFeatureSet;

use SJS\Flow\MCP\Domain\Connection\ServerContext;
use SJS\Flow\MCP\Domain\MCP\Tool;
use SJS\Flow\MCP\Domain\MCP\Tool\Annotations;
use SJS\Flow\MCP\Domain\MCP\Tool\Content;
use SJS\Flow\MCP\Domain\MCP\ToolConstructor;
use SJS\Flow\MCP\FeatureSet\FeatureSetInterface;
use SJS\Flow\MCP\JsonSchema\ObjectSchema;
use SJS\Flow\MCP\JsonSchema\StringSchema;

class HelloTool extends Tool implements ToolConstructor
{
    public function __construct(FeatureSetInterface $featureSet)
    {
        parent::__construct(
            name: 'hello',
            description: 'Returns a greeting for the given name.',
            inputSchema: new ObjectSchema(properties: [
                'name' => (new StringSchema(
                    description: "Name to greet"
                ))->required(),
            ]),
            annotations: new Annotations(
                title: 'Hello',
                readOnlyHint: true
            ),
            featureSet: $featureSet,
        );
    }

    public function run(ServerContext $serverContext, array $input): Content
    {
        $name = $input['name'];

        return Content::structuredWithFallback([
            'greeting' => "Hello, {$name}!",
        ]);
    }
}
```

### `Configuration/Settings.Server.yaml`

```yaml
SJS:
  Flow:
    MCP:
      server:
        mcp:
          featureSets:
            greeting: \Vendor\Site\FeatureSet\Example\GreetingFeatureSet
```

This registers the FeatureSet under the prefix `greeting`, producing the tool
name `greeting_hello`.

---

## Checklist

- [ ] `composer.json` requires `sjs/flow-mcp` (and `sjs/neos-mcp` if using Neos services)
- [ ] FeatureSet class in `Classes/`, annotated `#[Flow\Scope("singleton")]`
- [ ] FeatureSet `initialize()` calls `$this->addTool()` for each Tool
- [ ] Tool classes in a subdirectory named after the FeatureSet
- [ ] Each Tool extends `Tool` and implements `ToolConstructor`
- [ ] Tool constructors pass `name`, `description`, `inputSchema`, `annotations`, and `featureSet` to `parent::__construct()`
- [ ] Tool names use `snake_case` without the FeatureSet prefix
- [ ] Read-only tools set `readOnlyHint: true`; write tools set `destructiveHint: true`
- [ ] Output uses `Content::structuredWithFallback()` for structured data
- [ ] Shared logic extracted to an abstract base tool or trait (not duplicated)
- [ ] Use the MCP logger: `#[Flow\Inject(name: "SJS.Flow.MCP:MCPLogger", lazy: false)]`
- [ ] FeatureSet registered in `Configuration/Settings.Server.yaml`
- [ ] Write tools wrap operations in try/catch (or use `runSafely()` / `catchCRExceptions()`)
- [ ] Semantic configuration filenames: `Settings.<PackageKey>.yaml`
