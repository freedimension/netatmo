<?php
namespace freedimension\netatmo;

use freedimension\rest\rest;

class netatmo
{
	const SCOPE_READ_STATION = "read_station";
	protected $hAuthData    = null;
	protected $oRest        = null;
	protected $hCredentials = [];
	protected $hDevices     = [];
	protected $hUserData    = [];

	public function __construct (
		rest $oRest,
		$hCredentials
	){
		$oRest->setBaseUri('https://api.netatmo.net');
		$oRest->setHttpVersion(CURL_HTTP_VERSION_1_1);
		$oRest->setContentType($oRest::CONTENTTYPE_URL);
		$this->oRest = $oRest;
		$this->hCredentials = $hCredentials;
		$this->connect();
	}

	public function read ()
	{
		$sPath = "api/getmeasure";
	}

	public function readDevices ()
	{
		$sPath = "api/devicelist";
		$hData = [
			'access_token' => $this->getToken(),
		];
		$hDevices = $this->oRest->get($sPath, $hData, true);
		$this->hDevices = $hDevices;
	}

	public function readUser ()
	{
		$sPath = "api/getuser";
		$hData = [
			'access_token' => $this->getToken(),
		];
		$hUserData = $this->oRest->get($sPath, $hData, true);
		$this->hUserData = $hUserData;
	}

	protected function connect ()
	{
		$sPath = "oauth2/token";
		$hData = [
			"grant_type" => "password",
			"scope"      => self::SCOPE_READ_STATION,
		];
		$hData = array_merge($hData, $this->hCredentials);
		$hAuthData = $this->oRest->post($sPath, $hData, true);
		$hAuthData['expire_time'] = time() + $hAuthData['expires_in'];
		$this->hAuthData = $hAuthData;
	}

	protected function getToken ()
	{
		if ( $this->hAuthData['expire_time'] > time() - 10 )
		{
			$this->refreshToken();
		}
		return $this->hAuthData['access_token'];
	}

	protected function refreshToken ()
	{
		$sPath = 'oauth2/token';
		$hData = [
			"grant_type"    => "refresh_token",
			"refresh_token" => $this->hAuthData['refresh_token'],
			"client_id"     => $this->hCredentials['client_id'],
			"client_secret" => $this->hCredentials['client_secret'],
		];
		$hAuthData = $this->oRest->post($sPath, $hData, true);
		$hAuthData['expire_time'] = time() + $hAuthData['expires_in'];
		$this->hAuthData = array_merge($this->hAuthData, $hAuthData);
	}
}