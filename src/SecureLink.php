<?php


namespace Mingalevme\SecureLink;


class SecureLink
{
    protected $key;
    protected $signatureArgName;
    protected $expiresArgName;

    public function __construct($key, $options = [])
    {
        $this->key = $key;

        $this->signatureArgName = isset($options['signatureArgName'])
            ? $options['signatureArgName']
            : 'signature';

        $this->expiresArgName = isset($options['expiresArgName'])
            ? $options['expiresArgName']
            : 'expires';
    }

    /**
     * @param string $url
     * @param int $ttl
     * @return string
     */
    public function sign($url, $ttl = null)
    {
        $parts = parse_url($url);

        if (($ttl = intval($ttl))) {
            $url = build_url($parts, [
                'q' => [
                    $this->expiresArgName => time() + $ttl,
                ],
            ], $parts);
        }

        return build_url($parts, [
            'q' => [
                $this->signatureArgName => $this->signature($url),
            ],
        ]);
    }

    public function signature($url)
    {
        if (($parts = parse_url($url)) === false || empty($parts['path'])) {
            throw new \InvalidArgumentException('Invalid url');
        }

        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        } else {
            $query = [];
        }

        unset($query[$this->signatureArgName]);

        ksort($query);

        $message = $parts['path'] . '?' . http_build_query($query);

        return self::base64UrlEncode(md5($message  . ' ' . $this->key, true));
    }

    public function isValid($url)
    {
        if (($parts = parse_url($url)) === false || empty($parts['path']) || empty($parts['query'])) {
            return false;
        }

        parse_str($parts['query'], $query);

        if (empty($query[$this->signatureArgName])) {
            return false;
        }

        if ($query[$this->signatureArgName] !== $this->signature($url)) {
            return false;
        }

        if (isset($query[$this->expiresArgName])) {
            if (intval($query[$this->expiresArgName]) < time()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Encode a string to URL-safe base64
     *
     * @param $value
     * @return mixed
     */
    protected static function base64UrlEncode($value)
    {
        return str_replace(['+', '/'], ['-', '_'], base64_encode($value));
    }

    /**
     * Decode a string from URL-safe base64
     *
     * @param string $value
     * @return bool|string
     */
    protected static function base64UrlDecode($value)
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $value));
    }

    /**
     * Parse URL and return query string as assoc array
     *
     * @param string $url
     * @return array
     */
    protected static function parseQueryStringFromUrl($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);

        if (!$query) {
            return [];
        }

        parse_str($query, $result);

        return (array) $result;
    }
}
