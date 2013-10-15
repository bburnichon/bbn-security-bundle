<?php

namespace BBn\SecurityBundle\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class ApiKeyListener implements ListenerInterface
{
    protected $securityContext;
    protected $apiKeyName;
    protected $providerKey;

    public function __construct(SecurityContextInterface $securityContext, $apiKeyName, $providerKey)
    {
        $this->securityContext = $securityContext;
        $this->apiKeyName = $apiKeyName;
        $this->providerKey = $providerKey;
    }

    /**
     * This interface must be implemented by firewall listeners.
     *
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->query->has($this->apiKeyName)) {
            return;
        }

        $this->securityContext->setToken($this->createToken($request));
    }


    public function createToken(Request $request)
    {
        if (!$request->query->has($this->apiKeyName)) {
            throw new BadCredentialsException('No API key found');
        }

        return new PreAuthenticatedToken('anon.', $request->query->get($this->apiKeyName), $this->providerKey);
    }
}
