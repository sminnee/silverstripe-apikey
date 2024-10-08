<?php

namespace Sminnee\ApiKey;

use LogicException;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Control\Middleware\HTTPMiddleware;
use SilverStripe\Security\IdentityStore;

/**
 * Initialises the versioned stage when a request is made.
 *
 * @package framework
 * @subpackage control
 */
class ApiKeyRequestMiddleware implements HTTPMiddleware
{
    public function process(HTTPRequest $request, callable $delegate)
    {
        $headerName = Config::inst()->get(self::class, 'header_name');
        $key = $request->getHeader($headerName);
        if ($key) {
            try {
                $matchingKey = MemberApiKey::findByKey($key);
            } catch (LogicException $e) {
            }

            if (!$matchingKey) {
                throw new HTTPResponse_Exception("Bad X-API-Key", 400);
            }

            Injector::inst()->get(IdentityStore::class)->logIn($matchingKey->Member());

            $matchingKey->markUsed();
        }

        return $delegate($request);
    }
}
