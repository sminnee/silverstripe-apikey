<?php

namespace Sminnee\ApiKey;

use LogicException;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\Middleware\HTTPMiddleware;

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

            $matchingKey->Member()->logIn();

            $matchingKey->markUsed();
        }

        return $delegate($request);
    }
}
