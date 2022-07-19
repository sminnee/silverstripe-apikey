<?php

namespace Sminnee\ApiKey\GraphQL;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\GraphQL\Auth\AuthenticatorInterface;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Member;
use Sminnee\ApiKey\ApiKeyRequestMiddlware;
use Sminnee\ApiKey\MemberApiKey;

/**
 * Provides the ability to authenticate as a SilverStripe member in external
 * GraphQL requests by using the API key header.
 */
class ApiKeyAuthenticator implements AuthenticatorInterface
{
    public function authenticate(HTTPRequest $request): ?Member
    {
        $key = $this->getApiKeyHeader($request);
        // This should not happen, unless ::authenticate is called without ::isApplicable
        if (!$key) {
            throw new ValidationException('API key header not readable - please check system logs.');
        }

        $matchingKey = MemberApiKey::findByKey($key);
        if (!$matchingKey) {
            throw new ValidationException('Specified API key was not found.');
        }

        $member = $matchingKey->Member();
        if ($member instanceof Member && $member->exists()) {
            $matchingKey->markUsed();
            return $member;
        }

        throw new ValidationException('Specified API key is not assigned to any members.');
    }

    public function isApplicable(HTTPRequest $request): bool
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
        $headerName = Config::inst()->get(ApiKeyRequestMiddlware::class, 'header_name');

        return $request->getHeader($headerName) ?: false;
    }
}
