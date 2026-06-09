<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\MCP;

/**
 * Marker interface for Tools that receive their parent FeatureSet
 * via the constructor. addTool() checks for this interface before
 * passing the FeatureSet instance.
 *
 * Implementing classes MUST accept FeatureSetInterface as their
 * first constructor parameter.
 */
interface ToolConstructor
{
}