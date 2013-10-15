<?php

namespace BBn\SecurityBundle\Test\Firewall;


use BBn\SecurityBundle\Firewall\ApiKeyListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class ApiKeyListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testItImplementsSecurityListenerInterface()
    {
        $mockedSecurityContext = $this->getMockForAbstractClass('Symfony\\Component\\Security\\Core\\SecurityContextInterface');

        $listener = new ApiKeyListener($mockedSecurityContext, 'testApiKeyName', 'testProviderKey');

        $this->assertInstanceOf('Symfony\\Component\\Security\\Http\\Firewall\\ListenerInterface', $listener);
    }

    public function testItSavesConstructorParameters()
    {
        $mockedSecurityContext = $this->getMockForAbstractClass('Symfony\\Component\\Security\\Core\\SecurityContextInterface');

        $listener = new ApiKeyListener($mockedSecurityContext, 'testApiKeyName', 'testProviderKey');

        $this->assertAttributeSame($mockedSecurityContext, 'securityContext', $listener);
        $this->assertAttributeSame('testApiKeyName', 'apiKeyName', $listener);
        $this->assertAttributeSame('testProviderKey', 'providerKey', $listener);
    }

    public function testHandleReturnIfRequestHasNotRequiredParameter()
    {
        $mockedKernel = $this->getMockForAbstractClass('Symfony\\Component\\HttpKernel\\HttpKernelInterface');
        $request = new Request();
        $event = new GetResponseEvent($mockedKernel, $request, 'master');

        $mockedSecurityContext = $this->getMockForAbstractClass('Symfony\\Component\\Security\\Core\\SecurityContextInterface');
        $mockedSecurityContext->expects($this->never())
            ->method('setToken');

        $listener = new ApiKeyListener($mockedSecurityContext, 'testApiKeyName', 'testProviderKey');

        $listener->handle($event);
    }

    public function testHandleCreateTokenIfHasParameter()
    {
        $mockedKernel = $this->getMockForAbstractClass('Symfony\\Component\\HttpKernel\\HttpKernelInterface');
        $request = new Request(array('testApiKeyName' => 'testData'));
        $event = new GetResponseEvent($mockedKernel, $request, 'master');

        $token = new PreAuthenticatedToken('anon.', 'testData', 'testProviderKey');
        $mockedSecurityContext = $this->getMockForAbstractClass('Symfony\\Component\\Security\\Core\\SecurityContextInterface');
        $mockedSecurityContext->expects($this->once())
            ->method('setToken')
            ->will($this->returnValue($token));

        $listener = $this->getMock('BBn\\SecurityBundle\\Firewall\\ApiKeyListener', array('createToken'), array($mockedSecurityContext, 'testApiKeyName', 'testProvider'));
        $listener->expects($this->once())
            ->method('createToken')
            ->with($request)
            ->will($this->returnValue($token))
        ;

        $listener->handle($event);
    }

    public function testCreateTokenWithoutApiKey()
    {
        $mockedSecurityContext = $this->getMockForAbstractClass('Symfony\\Component\\Security\\Core\\SecurityContextInterface');

        $listener = new ApiKeyListener($mockedSecurityContext, 'testApiKeyName', 'testProviderKey');
        $request = new Request();

        try {
            $listener->createToken($request);
        } catch (BadCredentialsException $bce) {
            $this->assertContains('No API key found', $bce->getMessage());
            return;
        }
        $this->fail('Expected exception was not thrown');
    }

    public function testCreateTokenApiKey()
    {
        $mockedSecurityContext = $this->getMockForAbstractClass('Symfony\\Component\\Security\\Core\\SecurityContextInterface');

        $listener = new ApiKeyListener($mockedSecurityContext, 'testApiKeyName', 'testProviderKey');
        $request = new Request(array('testApiKeyName' => 'testData'));

        $token = $listener->createToken($request);

        $this->assertInstanceOf('Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken', $token);
        $this->assertEquals('anon.', $token->getUsername());
        $this->assertEquals('testData', $token->getCredentials());
    }
}
