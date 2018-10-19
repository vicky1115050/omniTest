<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Events extends Controller {

public $gclient;

	protected $access_token;

	protected $user_email;

	public function __construct(Request $request, Response $response){

    	parent::__construct($request, $response);

    	$google_client =  Model::factory('Gclient');
		$this->gclient = $google_client->client;

    	
    	$this->access_token = json_decode(file_get_contents(DOCROOT.'access_token.json'), true);

    	// If there is no previous token.
	    if ($this->access_token == null || $this->access_token == '') {
	       
            $auth_url = $this->gclient->createAuthUrl();
			$this->redirect(filter_var($auth_url, FILTER_SANITIZE_URL));

	    }


		$this->gclient->setAccessToken($this->access_token);

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

	}

	// Displays Success page with all calender events.
	public function action_index()
	{

		$this->store_events();

		$query = DB::select()->from('events');

		$events = $query->execute();

		$this->response->body(View::factory('events')->bind('events', $events));
	}

	// Crawler That pull events and stores in database.
	public function action_events()
	{
		
	    	$events = $this->store_events();
		  	$this->response->body(json_encode($events));


	}


	public function store_events()
	{
		// Get the API client and construct the service object.
		$service = new Google_Service_Calendar($this->gclient);

		// Print the next 10 events on the user's calendar.
		$calendarId = 'primary';
		$optParams = array(
		  'maxResults' => 100,
		  'orderBy' => 'startTime',
		  'singleEvents' => true
		);
		$results = $service->events->listEvents($calendarId, $optParams);
		$events = $results->getItems();

		// Add only new events.
		foreach ($events as $event) {

			$eve = json_encode($event);

			$query = DB::select()->from('events')->where('gid', '=', $event->id);

			$results = $query->execute();

			if(count($results) == 0){
				$new_event = array($eve, $event->id, $event->updated);
				$query = DB::insert('events', array('event_json', 'gid', 'updated'))->values($new_event);
				$result = $query->execute();			
			}
		}

		return $events;

	}

	// Watcher that updates any event changes.
	public function action_watch()
	{
			$updated = false;
			$message = '';
			
		    // Get the API client and construct the service object.
			$service = new Google_Service_Calendar($this->gclient);

			// Print the next 10 events on the user's calendar.
			$calendarId = 'primary';
			$optParams = array(
			  'maxResults' => 100,
			  'orderBy' => 'startTime',
			  'singleEvents' => true
			);
			$results = $service->events->listEvents($calendarId, $optParams);
			$events = $results->getItems();


			foreach ($events as $event) {

				$eve = json_encode($event);

				$query = DB::select()->from('events')->where('gid', '=', $event->id)->limit(1);

				$results = $query->execute();

				if(count($results) > 0){

					if($results[0]['updated'] !== $event->updated){

						$updated = true;

						$message .= '# Updated event: '.$event->summary.'!!<br>';

						$query = DB::update('events')->set(array('event_json' => $eve, 'gid' => $event->id, 'updated' => $event->updated))->where('gid', '=', $event->id);

						$result = $query->execute();			
					}
				}
			}		

			if($updated)
		  		$this->response->body(json_encode(array('SUCCESS' => 1 , 'DATA' => $message )));
		  	else
		  		$this->response->body(json_encode(array('SUCCESS' => 0, 'DATA' => 'No Updates')));
	}


}
