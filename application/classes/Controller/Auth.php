<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Auth extends Controller {

	public $gclient;

	protected $access_token;

	protected $user_email;

	public function __construct(Request $request, Response $response){

    	parent::__construct($request, $response);

    	$google_client =  Model::factory('Gclient');
    	$this->gclient = $google_client->client;

    	if(! $this->gclient)
    	{
            $this->redirect('/');    	
        }

        if (! isset($_GET['code'])) 
        {
		  $auth_url = $this->gclient->createAuthUrl();
		  //header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
		  $this->redirect(filter_var($auth_url, FILTER_SANITIZE_URL));
		} 
		else 
		{

		  $this->gclient->authenticate($_GET['code']);
		  $this->access_token = $this->gclient->getAccessToken();

		   // Store Access Token
		     file_put_contents(DOCROOT.'access_token.json', json_encode($this->gclient->getAccessToken()));
		 

		    // If there is no previous token or it's expired.
		    if ($this->access_token == null || $this->gclient->isAccessTokenExpired()) {
		        // Refresh the token if possible, else fetch a new one.
		        if ($this->gclient->getRefreshToken()) {
		            $this->gclient->fetchAccessTokenWithRefreshToken($this->gclient->getRefreshToken());
		        } else {
		            $auth_url = $this->gclient->createAuthUrl();
					$this->redirect(filter_var($auth_url, FILTER_SANITIZE_URL));
		        }

		    }

		     $this->gclient->setAccessToken($this->access_token);		    


		}

	}


	// Redirects to Success page.
	public function action_index()
	{	

	  $google_oauth = new Google_Service_Oauth2($this->gclient);
	  $google_account_email = $google_oauth->userinfo->get()->email;


	  $this->redirect('http://localhost:8080/success/' . $google_account_email);		
	}
	

}
