<?php

namespace BBn\SecurityBundle\Test\Authentication;

use BBn\SecurityBundle\Authentication\ApiKeyAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;

class ApiKeyAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockedUserProvider;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockedUserChecker;

    protected function setUp()
    {
        $this->mockedUserProvider = $this->getMock('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface');
        $this->mockedUserChecker = $this->getMock('Symfony\\Component\\Security\\Core\\User\\UserCheckerInterface');
    }

    public function testItImplementsAuthenticationProviderInterface()
    {
        $authenticator = $this->createAuthenticator();

        $this->assertInstanceOf('Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface', $authenticator);
    }

    public function testItSavesUserProviderAndProviderKey()
    {
        $authenticator = $this->createAuthenticator();

        $this->assertAttributeSame($this->mockedUserProvider, 'userProvider', $authenticator);
        $this->assertAttributeSame('testProviderKey', 'providerKey', $authenticator);
    }

    public function testItSupportPreAuthenticatedToken()
    {
        $authenticator = $this->createAuthenticator();

        $token = new PreAuthenticatedToken('testUser', 'testCredentials', 'invalidProviderKey');
        $this->assertFalse($authenticator->supports($token));

        $token = new PreAuthenticatedToken('testUser', 'testCredentials', 'testProviderKey');
        $this->assertTrue($authenticator->supports($token));
    }

    public function testAuthenticateTokenSearchUserFromCredentials()
    {
        $usernameNotFoundException = new UsernameNotFoundException();
        $this->mockedUserProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with('testCredentials')
            ->will($this->throwException($usernameNotFoundException))
        ;
        $this->mockedUserChecker
            ->expects($this->never())
            ->method('checkPostAuth')
        ;

        $authenticator = $this->createAuthenticator();
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
        $this->mockedUserProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with('testCredentials')
            ->will($this->returnValue($user))
        ;
        $this->mockedUserChecker
            ->expects($this->once())
            ->method('checkPostAuth')
            ->with($user)
        ;

        $authenticator = $this->createAuthenticator();
        $token = new PreAuthenticatedToken('dontCare', 'testCredentials', 'testProviderKey');

        $authToken = $authenticator->authenticateToken($token);
        $this->assertInstanceOf(get_class($token), $authToken);
        $this->assertNotSame($token, $authToken);
        $this->assertSame($user, $authToken->getUser());
    }

    protected function createAuthenticator()
    {
        return new ApiKeyAuthenticator($this->mockedUserProvider, $this->mockedUserChecker, 'testProviderKey');
    }
}
