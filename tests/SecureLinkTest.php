<?php


namespace Mingalevme\Tests\SecureLink;


use Mingalevme\SecureLink\SecureLink;
use PHPUnit\Framework\TestCase;

class SecureLinkTest extends TestCase
{
    const KEY = 'phpunit';

    /**
     * @var SecureLink
     */
    protected $signer;

    public function setUp()
    {
        parent::setUp();
        $this->signer = new SecureLink(self::KEY);
    }

    public function testWithoutQueryArgs()
    {
        $signedUrl = $this->signer->sign('https://github.com/mingalevme/secure-link-php');
        $this->assertSame('https://github.com/mingalevme/secure-link-php?signature=rHCxCclcrmvSBqTuy6DBpg%3D%3D', $signedUrl);
    }

    public function testWithQueryArgs()
    {
        $signedUrl = $this->signer->sign('https://github.com/mingalevme/secure-link-php?foo=bar&bar=foo');
        $this->assertSame('https://github.com/mingalevme/secure-link-php?foo=bar&bar=foo&signature=o0GfFEYD7trMDg8FL-wJ2Q%3D%3D', $signedUrl);
    }

    public function testWithQueryReorderedArgs()
    {
        $signedUrl = $this->signer->sign('https://github.com/mingalevme/secure-link-php?bar=foo&foo=bar&');
        $this->assertSame('https://github.com/mingalevme/secure-link-php?bar=foo&foo=bar&signature=o0GfFEYD7trMDg8FL-wJ2Q%3D%3D', $signedUrl);
    }

    public function testWithQueryArgsAndExpires()
    {
        $this->signer = new SecureLink(self::KEY, [
            'signatureArgName' => '_signature',
            'expiresArgName' => '_expires',
        ]);

        $signedUrl = $this->signer->sign('https://github.com/mingalevme/secure-link-php?bar=foo&foo=bar&', 3600);

        $query = parse_url($signedUrl, PHP_URL_QUERY);

        parse_str($query, $query);

        $this->assertArrayHasKey('_signature', $query);
        $this->assertArrayHasKey('_expires', $query);
        $this->assertArrayHasKey('bar', $query);
        $this->assertArrayHasKey('foo', $query);

        $this->assertTrue(intval($query['_expires']) - time() - 3600 <= 1);
    }

    public function testValidationWithoutExpires()
    {
        $signedUrl = $this->signer->sign('https://github.com/mingalevme/secure-link-php?bar=foo&foo=bar&');
        $this->assertTrue($this->signer->isValid($signedUrl));
    }

    public function testValidationWithFreshLink()
    {
        $signedUrl = $this->signer->sign('https://github.com/mingalevme/secure-link-php?bar=foo&foo=bar&', 3600);
        $this->assertTrue($this->signer->isValid($signedUrl));
    }

    public function testValidationWithStaleLink()
    {
        $signedUrl = $this->signer->sign('https://github.com/mingalevme/secure-link-php?bar=foo&foo=bar&', -3600);
        $this->assertFalse($this->signer->isValid($signedUrl));
    }

    public function testValidationWithInvalidSignature()
    {
        $signedUrl = $this->signer->sign('https://github.com/mingalevme/secure-link-php?bar=foo&foo=bar&');
        $this->assertFalse($this->signer->isValid($signedUrl . 'something'));
    }
}
