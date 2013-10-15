<?php

namespace BBn\SecurityBundle\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyAuthenticator implements AuthenticationProviderInterface
{
    private $userProvider;
    private $userChecker;
    private $providerKey;

    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey)
    {
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->providerKey = $providerKey;
    }

    public function authenticate(TokenInterface $token)
    {
        $authToken = $this->authenticateToken($token);

        if ($authToken instanceof TokenInterface) {
            return $authToken;
        }

        throw new AuthenticationException('ApiKey authenticator failed to return an authenticated token.');
    }

    public function authenticateToken(TokenInterface $token)
    {
        $apiKey = $token->getCredentials();
        try {
            $user = $this->userProvider->loadUserByUsername($apiKey);
        } catch (UsernameNotFoundException $e) {
            throw new AuthenticationException(sprintf('API Key "%s" does not exist.', $apiKey), 0, $e);
        }

        $this->userChecker->checkPostAuth($user);

        return new PreAuthenticatedToken($user, $apiKey, $this->providerKey, $user->getRoles());
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() == $this->providerKey;
    }
}
