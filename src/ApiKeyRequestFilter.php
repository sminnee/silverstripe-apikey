<?php

namespace Sminnee\ApiKey;

use LogicException;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\RequestFilter;
use SilverStripe\Control\Session;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataModel;

/**
 * Initialises the versioned stage when a request is made.
 *
 * @package framework
 * @subpackage control
 */
class ApiKeyRequestFilter implements RequestFilter
{
    public function preRequest(HTTPRequest $request)
    {
        $headerName = Config::inst()->get(self::class, 'header_name');

        if ($key = $request->getHeader($headerName)) {
            try {
                $matchingKey = MemberApiKey::findByKey($key);
            } catch (LogicException $e) {
            }

            if ($matchingKey) {
                // Log-in can't have session injected, we need to to push $session into the global state
                $controller = new Controller;
                $controller->setSession($session);
                $controller->pushCurrent();

                $matchingKey->Member()->logIn();

                // Undo our global state manipulation
                $controller->popCurrent();

                $matchingKey->markUsed();
            } else {
                throw new HTTPResponse_Exception("Bad X-API-Key", 400);
            }
        }

        return true;
    }

    public function postRequest(HTTPRequest $request, HTTPResponse $response)
    {
        return true;
    }
}
