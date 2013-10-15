<?php

namespace BBn\SecurityBundle\Test\Authentication;

use BBn\SecurityBundle\Authentication\ApiKeyAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;

class ApiKeyAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    public function testItImplementsAuthenticationProviderInterface()
    {
        $mockedUserProvider = $this->getMockForAbstractClass('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface');

        $authenticator = new ApiKeyAuthenticator($mockedUserProvider, 'testProviderKey');

        $this->assertInstanceOf('Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface', $authenticator);
    }

    public function testItSavesUserProviderAndProviderKey()
    {
        $mockedUserProvider = $this->getMockForAbstractClass('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface');

        $authenticator = new ApiKeyAuthenticator($mockedUserProvider, 'testProviderKey');

        $this->assertAttributeSame($mockedUserProvider, 'userProvider', $authenticator);
        $this->assertAttributeSame('testProviderKey', 'providerKey', $authenticator);
    }

    public function testItSupportPreAuthenticatedToken()
    {
        $mockedUserProvider = $this->getMockForAbstractClass('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface');

        $authenticator = new ApiKeyAuthenticator($mockedUserProvider, 'testProviderKey');

        $token = new PreAuthenticatedToken('testUser', 'testCredentials', 'invalidProviderKey');
        $this->assertFalse($authenticator->supports($token));

        $token = new PreAuthenticatedToken('testUser', 'testCredentials', 'testProviderKey');
        $this->assertTrue($authenticator->supports($token));
    }

    public function testAuthenticateTokenSearchUserFromCredentials()
    {
        $usernameNotFoundException = new UsernameNotFoundException();
        $mockedUserProvider = $this->getMockForAbstractClass('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface');
        $mockedUserProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with('testCredentials')
            ->will($this->throwException($usernameNotFoundException))
        ;

        $authenticator = new ApiKeyAuthenticator($mockedUserProvider, 'testProviderKey');
        $token = new PreAuthenticatedToken('dontCare', 'testCredentials', 'testProviderKey');

        try {
            $authenticator->authenticateToken($token);
        } catch (AuthenticationException $ae) {
            $this->assertContains('does not exist', $ae->getMessage());
            $this->assertSame($usernameNotFoundException, $ae->getPrevious());
            return;
        }

        $this->fail('An exception should have occured');
    }


    public function testAuthenticateTokenReturnPreAuthenticatedToken()
    {
        $user = new User('testUserName', 'testCredentials');
        $mockedUserProvider = $this->getMockForAbstractClass('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface');
        $mockedUserProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with('testCredentials')
            ->will($this->returnValue($user))
        ;

        $authenticator = new ApiKeyAuthenticator($mockedUserProvider, 'testProviderKey');
        $token = new PreAuthenticatedToken('dontCare', 'testCredentials', 'testProviderKey');

        $authToken = $authenticator->authenticateToken($token);
        $this->assertInstanceOf(get_class($token), $authToken);
        $this->assertNotSame($token, $authToken);
        $this->assertSame($user, $authToken->getUser());
    }
}
