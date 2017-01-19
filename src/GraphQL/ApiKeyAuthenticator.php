<?php

namespace Sminnee\ApiKey\GraphQL;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\GraphQL\Auth\AuthenticatorInterface;
use SilverStripe\Security\Member;
use Sminnee\ApiKey\ApiKeyRequestFilter;
use Sminnee\ApiKey\MemberApiKey;

/**
 * Provides the ability to authenticate as a SilverStripe member in external
 * GraphQL requests by using the API key header.
 */
class ApiKeyAuthenticator implements AuthenticatorInterface
{
    public function authenticate(HTTPRequest $request)
    {
        $key = $this->getApiKeyHeader($request);
        if (!$key) {
            return null;
        }

        $matchingKey = MemberApiKey::findByKey($key);
        if (!$matchingKey) {
            return null;
        }

        $member = $matchingKey->Member();
        if ($member instanceof Member) {
            $matchingKey->markUsed();
            return $member;
        }

        return null;
    }

    public function isApplicable(HTTPRequest $request)
    {
        return (bool) $this->getApiKeyHeader($request);
    }

    /**
     * Look up the header name from configuration, and return it or false from
     * the given request
     *
     * @param  HTTPRequest $request
     * @return string|false
     */
    protected function getApiKeyHeader(HTTPRequest $request)
    {
        $headerName = Config::inst()->get(ApiKeyRequestFilter::class, 'header_name');

        return $request->getHeader($headerName) ?: false;
    }
}
