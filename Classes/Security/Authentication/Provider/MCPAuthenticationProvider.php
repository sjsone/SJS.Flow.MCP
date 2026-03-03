<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Security\Authentication\Provider;

use Neos\Flow\Security\Account;
use Neos\Flow\Security\Authentication\AuthenticationProviderInterface;
use Neos\Flow\Security\Authentication\Provider\AbstractProvider;
use Neos\Flow\Security\Authentication\TokenInterface;
use SJS\Flow\MCP\Domain\Model\Agent;
use SJS\Flow\MCP\Domain\Provider\AgentProviderInterface;
use SJS\Flow\MCP\Security\Authentication\Token\MCPToken;
use Neos\Flow\Annotations as Flow;


class MCPAuthenticationProvider extends AbstractProvider implements AuthenticationProviderInterface
{

    #[Flow\Inject]
    protected AgentProviderInterface $agentProvider;

    public function getTokenClassNames()
    {
        return [MCPToken::class];
    }

    public function authenticate(TokenInterface $authenticationToken)
    {


        $token = $authenticationToken->getCredentials()["bearer"] ?? null;
        if ($token === null) {
            $authenticationToken->setAuthenticationStatus(TokenInterface::WRONG_CREDENTIALS);
            return;
        }


        $agent = $this->agentProvider->getAgentByToken($token);
        if ($agent === null) {
            $authenticationToken->setAuthenticationStatus(TokenInterface::WRONG_CREDENTIALS);
            return;
        }

        $account = $this->getAccountFromAgent($agent);
        if ($account === null) {
            $authenticationToken->setAuthenticationStatus(TokenInterface::WRONG_CREDENTIALS);
            return;
        }

        $authenticationToken->setAccount($account);

        $authenticationToken->setAuthenticationStatus(TokenInterface::AUTHENTICATION_SUCCESSFUL);
    }

    protected function getAccountFromAgent(Agent $agent): ?Account
    {
        $account = $agent->account;
        return $account;
    }
}