<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Security\Authentication\Token;

use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\Authentication\EntryPointInterface;
use Neos\Flow\Security\Authentication\Token\AbstractToken;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Exception\InvalidAuthenticationStatusException;
use Neos\Flow\Security\RequestPatternInterface;
use SJS\Flow\MCP\Domain\Server\ServerFactory;

class MCPToken extends AbstractToken implements TokenInterface
{
    public function updateCredentials(ActionRequest $actionRequest)
    {
        $this->setAuthenticationStatus(self::AUTHENTICATION_NEEDED);
        $httpRequest = $actionRequest->getHttpRequest();

        if (!$httpRequest->hasHeader('Authorization')) {
            return;
        }

        $this->setAuthenticationStatus(TokenInterface::NO_CREDENTIALS_GIVEN);

        foreach ($httpRequest->getHeader('Authorization') as $authorizationHeader) {
            if (strpos($authorizationHeader, 'Bearer ') === 0) {
                $this->credentials['bearer'] = \substr($authorizationHeader, \strlen('Bearer '));

                $serverName = ServerFactory::extractServerNameFromActionRequest($actionRequest);
                $this->credentials['serverName'] = $serverName;

                $this->setAuthenticationStatus(TokenInterface::AUTHENTICATION_NEEDED);
                return;
            }
        }
    }
}
