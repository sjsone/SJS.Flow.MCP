<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Security\Authentication\Provider;

use Neos\Flow\Security\Authentication\AuthenticationProviderInterface;
use Neos\Flow\Security\Authentication\Provider\AbstractProvider;
use Neos\Flow\Security\Authentication\TokenInterface;
use SJS\Flow\MCP\Domain\Connection\ConnectionProviderInterface;
use SJS\Flow\MCP\Security\Authentication\Token\MCPToken;
use Neos\Flow\Annotations as Flow;


class MCPAuthenticationProvider extends AbstractProvider implements AuthenticationProviderInterface
{

    #[Flow\Inject]
    protected ConnectionProviderInterface $connectionProvider;


    /**
     * @return array<class-string>
     */
    public function getTokenClassNames(): array
    {
        return [MCPToken::class];
    }

    public function authenticate(TokenInterface $authenticationToken)
    {

        $credentials = $authenticationToken->getCredentials();
        if (!\is_array($credentials) || !isset($credentials["bearer"])) {
            return;
        }

        $token = $credentials["bearer"] ?? null;
        if ($token === null || !\is_string($token)) {
            $authenticationToken->setAuthenticationStatus(TokenInterface::WRONG_CREDENTIALS);
            return;
        }

        $serverName = $credentials["serverName"] ?? null;
        if ($serverName === null || !\is_string($serverName)) {
            $authenticationToken->setAuthenticationStatus(TokenInterface::WRONG_CREDENTIALS);
            return;
        }

        $connection = $this->connectionProvider->getConnectionByTokenAndServerName($token, $serverName);
        if ($connection === null) {
            $authenticationToken->setAuthenticationStatus(TokenInterface::WRONG_CREDENTIALS);
            return;
        }

        $account = $connection->account;
        $authenticationToken->setAccount($account);

        $authenticationToken->setAuthenticationStatus(TokenInterface::AUTHENTICATION_SUCCESSFUL);
    }
}
