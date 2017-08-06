<?php

namespace stevotvr\steamstatus\controller;

use \stevotvr\steamstatus\util\steamstatus;
use \Symfony\Component\HttpFoundation\JsonResponse;

class main
{
	private $cache;

	private $config;

	private $language;

	private $request;

	public function __construct(\phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\language\language $language, \phpbb\request\request $request)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->language = $language;
		$this->request = $request;

		$language->add_lang('common', 'stevotvr/steamstatus');
	}

	public function handle()
	{
		$api_key = $this->config['stevotvr_steamstatus_api_key'];
		if (empty($api_key))
		{
			return new JsonResponse(null, 500);
		}

		$output = array();
		$steamids = $this->request->variable('steamids', '', false, \phpbb\request\request_interface::GET);
		if (!empty($steamids))
		{
			$steamids = array_unique(array_map('trim', explode(',', $steamids)));
			$steamids = self::get_valid_ids($steamids);
			$output = steamstatus::get_from_api($api_key, $steamids, $this->cache);
		}

		foreach ($output as &$profile)
		{
			$profile = steamstatus::get_localized_data($profile, $this->language);
		}

		return new JsonResponse(array('status' => $output));
	}

	static private function get_valid_ids(array $unsafe)
	{
		$safe = array();
		foreach ($unsafe as $steamid)
		{
			if (preg_match('/^\d{17}$/', $steamid))
			{
				$safe[] = $steamid;
			}
		}
		return $safe;
	}
}
