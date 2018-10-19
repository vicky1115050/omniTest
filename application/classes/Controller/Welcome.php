<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller {


	// Displays Index page.
	public function action_index()
	{
		$this->response->body(View::factory('welcome'));
	}


	public function action_auth()
	{		
		$this->response->body('SUCCESS');
	}


	// Logs out of App
	public function action_logout()
	{	

		
		  // Clear previous user data for privacy reason;
		  $query = DB::delete('events');
		  $query->execute();

		 // Store Access Token
         file_put_contents(DOCROOT.'access_token.json', '');

	  	 $this->redirect('http://localhost:8080/');		
	}


}
