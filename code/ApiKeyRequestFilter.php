<?php

	/**
	 * Initialises the versioned stage when a request is made.
	 *
	 * @package    framework
	 * @subpackage control
	 */
	class ApiKeyRequestFilter implements RequestFilter
	{

		public function preRequest(SS_HTTPRequest $request, Session $session, DataModel $model)
		{

			$headerName = Config::inst()
								->get('ApiKeyRequestFilter', 'header_name');

			if($key = $request->getHeader($headerName))
			{
				try
				{
					$matchingKey = MemberApiKey::findByKey($key);
				}catch(LogicException $e)
				{
				}

				if($matchingKey)
				{
					// Log-in can't have session injected, we need to to push $session into the global state
					$controller = new Controller;
					$controller->setSession($session);
					$controller->pushCurrent();

					$matchingKey->Member()
								->logIn();

					// Undo our global state manipulation
					$controller->popCurrent();

					$matchingKey->markUsed();

				}else
				{
					throw new SS_HTTPResponse_Exception("Bad X-API-Key", 400);
				}
			}

			return true;
		}

		public function postRequest(SS_HTTPRequest $request, SS_HTTPResponse $response, DataModel $model)
		{

			if($member = Member::currentUser())
			{
				$member->logout();
			}

			return true;
		}

	}
