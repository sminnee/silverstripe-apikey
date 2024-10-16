<?php

namespace Sminnee\ApiKey\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;
use Sminnee\ApiKey\MemberApiKey;
use Sminnee\ApiKey\ApiKeyMemberExtension;
use Sminnee\ApiKey\ApiKeyRequestMiddleware;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Security;

class ApiKeyRequestMiddlewareTest extends SapphireTest
{
    /**
     * @var Member
     */
    private $member;

    /**
     * @var MemberApiKey
     */
    private $key;

    /**
     * @var string
     */
    private $headerName;

    /**
     * @var string
     */
    protected static $fixture_file = 'ApiKeyRequestMiddlewareTest.yml';

    /**
    * @var array
    */
    protected static $required_extensions = [
        Member::class => [
            ApiKeyMemberExtension::class,
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = $this->objFromFixture(Member::class, 'admin');
        $this->key = MemberApiKey::createKey($this->member->ID);
        $this->headerName = Config::inst()->get(ApiKeyRequestMiddleware::class, 'header_name');
    }

    private function createDelegate(&$called)
    {
        return function() use (&$called) {
            $called = true;
        };
    }

    public function testPassthroughProcess()
    {
        // pass through with no login
        $request = new HTTPRequest('GET', Director::absoluteBaseURL() . '/api/v1');
        $middleware = new ApiKeyRequestMiddleware();

        $called = false;
        $delegate = $this->createDelegate($called);

        $middleware->process($request, $delegate);
        $this->assertTrue($called);
    }

    public function testBadProcess()
    {
        Injector::inst()->get(IdentityStore::class)->logOut();
        $called = false;
        $delegate = $this->createDelegate($called);
        $middleware = new ApiKeyRequestMiddleware();

        $this->expectException(HTTPResponse_Exception::class);
        $this->expectExceptionMessage(
            'Bad X-API-Key'
        );

        $request = new HTTPRequest('GET', Director::absoluteBaseURL() . '/api/v1');
        $request->addHeader($this->headerName, 'fakey');
        $middleware->process($request, $delegate);
        $this->assertFalse($called);
    }

    public function testSuccessProcess()
    {
        Injector::inst()->get(IdentityStore::class)->logOut();
        $called = false;
        $delegate = $this->createDelegate($called);
        $middleware = new ApiKeyRequestMiddleware();

        $request = new HTTPRequest('GET', Director::absoluteBaseURL() . '/api/v1');
        $request->addHeader($this->headerName, $this->key->ApiKey);
        $middleware->process($request, $delegate);
        $this->assertTrue($called);
        $loggedInUser = Security::getCurrentUser();
        $this->assertEquals($this->member->ID, $loggedInUser->ID);
    }
}
