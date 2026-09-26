# SJS.Flow.MCP

## Configuration

Feature sets are registered per server under `SJS.Flow.MCP.server.<server>.featureSets`.

### Disabling tools

Single tools can be switched off without touching the package that provides them. Use the
full tool name as it appears in `tools/list`, i.e. including the prefix:

```yaml
SJS:
  Flow:
    MCP:
      server:
        mcp:
          disabledTools:
            - workspace_publish_workspace
            - workspace_delete_workspace
```

A disabled tool is not listed and a call to it is answered like a call to an unknown tool.

### Disabling a whole feature set

Set the feature set registered by another package to `~`:

```yaml
SJS:
  Flow:
    MCP:
      server:
        mcp:
          featureSets:
            workspace: ~
```

Your package has to load after the package registering the feature set (require it in your
`composer.json`), or put this into the global `Configuration/Settings.yaml`.
