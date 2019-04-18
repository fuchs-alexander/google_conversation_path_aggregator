<?php

namespace Colibo\GoogleConversationPathAggregator;

class GoogleAnalyticsWrapper
{
	/** @var \Google_Service_Analytics */
	private $analyticsService;
	/** @var string */
	private $analyticsId;

	public function __construct(\Google_Client $client, $analyticsId)
	{
		$this->analyticsService = new \Google_Service_Analytics($client);
		$this->analyticsId = $analyticsId;
	}

	public function getAnalyticsService()
	{
		return $this->analyticsService;
	}

	public function getAnalyticsId()
	{
		return $this->analyticsId;
	}
}