bbn-security-bundle [![Build status...](https://secure.travis-ci.org/bburnichon/bbn-security-bundle.png?branch=master)](http://travis-ci.org/bburnichon/bbn-security-bundle)
===================

API Key based Authentication bundle for Symfony2

### Usage

This bundle will enable the new api_key authentication provider

use it as below in your security.yml file
```yaml
security:
    firewall:
        your-firewall-name:
            pattern: ^/what-you-wish-to-protect/
            provider: user_provider_name
            api_key:
                parameter: apikey
            stateless: true
```

The new user provider should provide api keys as username

The ```loadUserFromUsername()``` method will be called with the supplied api key the Authentication class does not care about the credentials fields

### Installation

```bash
$ composer require bburnichon/bbn-security-bundle:@dev
```

Then add the following to your AppKernel

```php
$bundles = array(
    new BBn\SecurityBundle\BBnSecurityBundle(),
);
```
### Running the Tests

```bash
$ php bin/phpunit
```

### License

bbn-security-bundle is released under the MIT License. See the bundled [LICENSE](https://github.com/bburnichon/bbn-security-bundle/blob/master/Resources/meta/LICENSE) file for details.

