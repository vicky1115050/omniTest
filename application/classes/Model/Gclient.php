<?php defined('SYSPATH') or die('No direct script access.');

include_once './vendor/autoload.php';

class Model_Gclient extends Model
{

	public $client;

    public function __construct()
    {

		$this->client = new Google_Client();
		$this->client->setApplicationName('Omnify');
		$this->client->setAccessType("offline");
		$this->client->setApprovalPrompt('force');
		$this->client->setAuthConfig(DOCROOT . 'oauth.json');
		$this->client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
		$this->client->addScope(Google_Service_Calendar::CALENDAR_READONLY);
		$this->client->setRedirectUri('http://localhost:8080/auth');
    }


}