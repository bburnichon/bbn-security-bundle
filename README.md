bbn-security-bundle [![Build status...](https://secure.travis-ci.org/bburnichon/bbn-security-bundle.png?branch=master)](http://travis-ci.org/bburnichon/bbn-security-bundle)
===================

API Key based Authentication bundle for Symfony2

### Usage

...

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

This will enable the new api_key authentication provider

### Running the Tests

```bash
$ php bin/phpunit
```

### License

bbn-security-bundle is released under the MIT License. See the bundled [LICENSE](https://github.com/bburnichon/bbn-security-bundle/blob/master/Resources/meta/LICENSE) file for details.

