# SecureLink
Simple HMAC implementation with TTL support for public urls

# Travis CI
[![Build Status](https://travis-ci.org/mingalevme/phputils-secure-link.svg?branch=master)](https://travis-ci.org/mingalevme/phputils-secure-link)

# Codecov
[![codecov](https://codecov.io/gh/mingalevme/phputils-secure-link/branch/master/graph/badge.svg)](https://codecov.io/gh/mingalevme/phputils-secure-link)

# Installation

1. ```composer require mingalevme/phputils-secure-link-php```.

2. Now you are able to use the tool:

```php
<?php

const SECRET = 'YOUR_SECRET_KEY';

use Mingalevme\Utils\Url\SecureLink;

$signer1 = new SecureLink(SECRET);

echo $signer1->sign('https://github.com/mingalevme/secure-link-php');
// https://github.com/mingalevme/secure-link-php?signature=13-dGaz-frzJ9qUg3iQ0RA%3D%3D

echo $signer1->sign('https://github.com/mingalevme/secure-link-php', 3600); 
// https://github.com/mingalevme/secure-link-php?expires=1526392953&signature=GOzCrktWlWDvSWVH49qjUQ%3D%3D

$signer2 = new SecureLink(SECRET, [
    'signatureArgName' => '_sig',
    'expiresArgName' => '_expires',
]);

echo $signer2->sign('https://github.com/mingalevme/secure-link-php', 3600);
// https://github.com/mingalevme/secure-link-php?_expires=1526393056&_sig=biyetWW5IgBPUftLF1SaOw%3D%3D
```

And validation

```php
<?php

const SECRET = 'YOUR_SECRET_KEY';

use Mingalevme\Utils\Url\SecureLink;

$signer1 = new SecureLink(SECRET);

if (!$signer1->isValid('https://github.com/mingalevme/secure-link-php?_expires=1526393056&_sig=biyetWW5IgBPUftLF1SaOw%3D%3D')) {
    throw new Exception('Url is invalid or expired');
}

```
