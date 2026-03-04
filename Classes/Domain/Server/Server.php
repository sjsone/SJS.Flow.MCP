<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\ObjectManagement\ObjectManager;
use Psr\Log\LoggerInterface;
use SJS\Flow\MCP\Domain\Client\Request;
use SJS\Flow\MCP\Domain\MCP\Completion;
use SJS\Flow\MCP\Domain\Server\Method;
use SJS\Flow\MCP\FeatureSet\FeatureSetInterface;
use SJS\Flow\MCP\Transport\JsonRPC;
use SJS\Flow\MCP\Transport\JsonRPC\ErrorCode;
use SJS\Flow\MCP\Transport\JsonRPC\Response;

#[Flow\Proxy(false)]
class Server
{
    /**
     * @var array<FeatureSetInterface>
     */
    protected array $featureSets = [];

    /**
     * @param array<string,mixed> $configuration
     */
    public function __construct(
        public readonly string $name,
        public readonly array $configuration,
        public readonly ActionRequest $request,
        protected ObjectManager $objectManager,
        protected LoggerInterface $logger,
    ) {
        $this->initializeFeatureSets();
    }

    protected function initializeFeatureSets(): void
    {
        $featureSetsConfiguration = $this->configuration['featureSets'] ?? [];
        if (!\is_array($featureSetsConfiguration)) {
            throw new \Exception("'featureSets' in configuration must be an array");
        }

        foreach ($featureSetsConfiguration as $featureSetName => $featureSetClass) {
            if (!\is_string($featureSetClass)) {
                throw new \Exception("value of featureSet configuration must be a string");

            }

            $featureSet = $this->objectManager->get($featureSetClass);

            if (!($featureSet instanceof FeatureSetInterface)) {
                continue;
            }
            $featureSet->setActionRequest($this->request);
            $featureSet->initialize();
            $this->featureSets[$featureSetName] = $featureSet;
        }
    }

    public function handleRequest(): string
    {
        $rpcRequest = $this->getRpcRequest();
        try {
            return $this->matchAndHandleRpcRequest($rpcRequest);
        } catch (\Throwable $th) {
            return $this->handleCaughtThrowable($th, $rpcRequest);
        }
    }

    protected function getRpcRequest(): JsonRPC\Request
    {
        /** @var array<string,mixed> */
        $rpcRequestData = $this->request->getArguments();

        $rpcRequestJson = json_encode($rpcRequestData, JSON_PRETTY_PRINT);
        $this->logger->debug("Request: {$rpcRequestJson}", LogEnvironment::fromMethodName(__METHOD__));

        return JsonRPC\Request::fromArray($rpcRequestData);
    }

    protected function matchAndHandleRpcRequest(JsonRPC\Request $rpcRequest): string
    {
        $response = "";
        $response = match ($rpcRequest->method) {
            Request\InitializeRequest::Method => $this->handleInitialize(Request\InitializeRequest::fromJsonRPCRequest($rpcRequest)),
            Request\Resources\ListRequest::Method => $this->handleResourcesList(Request\Resources\ListRequest::fromJsonRPCRequest($rpcRequest)),
            Request\Resources\Templates\ListRequest::Method => $this->handleResourcesTemplatesList(Request\Resources\Templates\ListRequest::fromJsonRPCRequest($rpcRequest)),
            Request\Resources\ReadRequest::Method => $this->handleResourcesRead(Request\Resources\ReadRequest::fromJsonRPCRequest($rpcRequest)),
            Request\Tools\ListRequest::Method => $this->handleToolsList(Request\Tools\ListRequest::fromJsonRPCRequest($rpcRequest)),
            Request\Tools\CallRequest::Method => $this->handleToolsCall(Request\Tools\CallRequest::fromJsonRPCRequest($rpcRequest)),
            Request\Completion\CompleteRequest::Method => $this->handleCompletionComplete(Request\Completion\CompleteRequest::fromJsonRPCRequest($rpcRequest)),
            Request\Notifications\Initialized::Method => $this->handleNotification(),
            Request\Notifications\CancelledRequest::Method => $this->handleNotification(),
            default => throw new \Exception("Unknown request method: {$rpcRequest->method}")
        };

        $this->logger->debug("Response: {$response}", LogEnvironment::fromMethodName(__METHOD__));

        return $response;
    }

    protected function handleCaughtThrowable(\Throwable $throwable, JsonRPC\Request $rpcRequest): string
    {
        $this->logger->critical("Caught error: " . $throwable->getMessage());

        $id = $rpcRequest->id;
        if ($id === null) {
            throw new \InvalidArgumentException("id in request is null");
        }

        $response = (new Response($id))->error($throwable->getMessage(), ErrorCode::INTERNAL_ERROR);
        return $response;
    }

    protected function handleInitialize(Request\InitializeRequest $initializeRequest): string
    {
        return Method\InitializeMethod::handle($initializeRequest);
    }

    protected function handleResourcesList(Request\Resources\ListRequest $resourcesListRequest): string
    {
        $resources = [];

        foreach ($this->featureSets as $featureSet) {
            $resources = [...$resources, ...$featureSet->resourcesList($resourcesListRequest->cursor)];
        }

        return Method\Resources\ListMethod::handle($resourcesListRequest, $resources, null);
    }

    protected function handleResourcesTemplatesList(Request\Resources\Templates\ListRequest $resourcesTemplatesListRequest): string
    {
        $templates = [];

        foreach ($this->featureSets as $featureSet) {
            $templates = [...$templates, ...$featureSet->resourcesTemplatesList()];
        }

        return Method\Resources\Templates\ListMethod::handle($resourcesTemplatesListRequest, $templates);
    }

    protected function handleCompletionComplete(Request\Completion\CompleteRequest $completionCompleteRequest): string
    {
        $completion = new Completion(
            [],
            0,
            false
        );

        foreach ($this->featureSets as $featureSet) {
            $featureSetCompletion = $featureSet->completionComplete($completionCompleteRequest->argument, $completionCompleteRequest->ref);
            if ($featureSetCompletion) {
                $completion = $featureSetCompletion;
                break;
            }
        }

        return Method\Completion\CompleteMethod::handle($completionCompleteRequest, $completion);
    }

    protected function handleResourcesRead(Request\Resources\ReadRequest $resourcesReadRequest): string
    {
        $resources = [];
        foreach ($this->featureSets as $featureSet) {
            $resources = [...$resources, ...$featureSet->resourcesRead($resourcesReadRequest->uri)];
        }

        return Method\Resources\ReadMethod::handle($resourcesReadRequest, $resources);
    }

    protected function handleToolsList(Request\Tools\ListRequest $toolsListRequest): string
    {
        $tools = [];
        foreach ($this->featureSets as $featureSet) {
            $tools = [...$tools, ...$featureSet->toolsList()];
        }

        return Method\Tools\ListMethod::handle($toolsListRequest, $tools, null);
    }

    protected function handleToolsCall(Request\Tools\CallRequest $toolsCallRequest): string
    {
        foreach ($this->featureSets as $featureSet) {
            if (!$featureSet->hasTool($toolsCallRequest->name)) {
                continue;
            }

            $requestArguments = $toolsCallRequest->arguments;

            $content = $featureSet->toolsCall($toolsCallRequest->name, $requestArguments);
            return Method\Tools\CallMethod::handle($toolsCallRequest, $content);
        }

        $response = new Response($toolsCallRequest->id);
        return $response->error("Unknown tool: {$toolsCallRequest->name}", ErrorCode::INVALID_PARAMS);
    }

    protected function handleNotification(): string
    {
        // TODO: handle notifications
        return "";
    }
}
