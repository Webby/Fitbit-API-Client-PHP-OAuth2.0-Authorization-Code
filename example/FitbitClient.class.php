<?php
/**
* \author Anthony Desvernois
* \brief PHP Client (authorization code flow) for Fitbit API, following OAuth2.0 and using only Curl
*
*/
require_once('config.php');

class FitbitClient {
      private $accessToken = null;
      private $refreshToken = null;
      private $expires = 0;

      /**
      * \fn getAuthorizationCode() launch the authorization page
      *
      */
      public static function getAuthorizationCode() {
      	     $url = AUTHENTICATE_URL.'?response_type=code&client_id='.CLIENT_ID.
	     	  '&redirect_uri='.urlencode(REDIRECT_URI).
		  '&scope=activity%20heartrate%20location%20nutrition%20profile'.
		  '%20settings%20sleep%20social%20weight&expires_in=604800';
	     header('Location: '.$url);
      }

      /**
      * \fn __construct() when used with an authorization code, get an access token
      * \param code optional paramater used to get an access token
      *
      */
      public function __construct($code = null) {
      	     if ($code == null)
	     	return;
      	     $ch = curl_init();
	     $data = array('client_id' => CLIENT_ID,
	     	   'grant_type' => 'authorization_code',
		   'redirect_uri' => REDIRECT_URI,
		   'code' => $code);
	     $options = array(CURLOPT_URL => ACCESS_TOKEN_URL,
	     	      CURLOPT_RETURNTRANSFER => true,
	     	      CURLOPT_POST => true,
		      CURLOPT_HEADER => false,
		      CURLOPT_POSTFIELDS => http_build_query($data),
	     	      CURLOPT_HTTPHEADER => array('Authorization: Basic '.base64_encode(CLIENT_ID.':'.CLIENT_SECRET),
		      		     	'Content-type: application/x-www-form-urlencoded'));
	     curl_setopt_array($ch, $options);
	     $json = json_decode(curl_exec($ch), true);
	     $this->accessToken = $json['access_token'];
	     $this->refreshToken = $json['refresh_token'];
	     $this->expires = time() + $json['expires_in'];
      }


      /**
      * \fn getParamters() get the internal paramters of the objet
      * \return array containing internal parameters - accessToken, refreshToken and expires timestamp
      *
      */
      public function getParameters() {
      	     return array('accessToken' => $this->accessToken,
	     	    'refreshToken' => $this->refreshToken,
		    'expires' => $this->expires);
      }

      /**
      * \fn setParamters() used to set the internal parameters of the object
      * \param parameters internal parameters - array containing accessToken, refreshToken and expires timestamp
      *
      */
      public function setParameters($parameters) {
      	     $this->accessToken = $parameters['accessToken'];
	     $this->refreshToken = $parameters['refreshToken'];
	     $this->expires = $parameters['expires'];
      }

      /**
      * \fn refreshToken() if needed, exchanges the accessToken and refreshToken for new ones
      *
      */
      private function refreshToken() {
      	      if (time() < $this->expires)
	      	 return;
      	      $ch = curl_init();
	      $data = array('client_id' => CLIENT_ID,
	      	    'grant_type' => 'refresh_token',
	      	    'refresh_token' => $this->refreshToken);
	      $options = array(CURLOPT_URL => ACCESS_TOKEN_URL,
	      	       CURLOPT_RETURNTRANSFER => true,
	      	       CURLOPT_POST => true,
	      	       CURLOPT_HEADER => false,
	      	       CURLOPT_POSTFIELDS => http_build_query($data),
	      	       CURLOPT_HTTPHEADER => array('Authorization: Basic '.base64_encode(CLIENT_ID.':'.CLIENT_SECRET),
	      	       			  'Content-type: application/x-www-form-urlencoded'));
	      curl_setopt_array($ch, $options);
	      $json = json_decode(curl_exec($ch), true);
	      $this->accessToken = $json['access_token'];
	      $this->refreshToken = $json['refresh_token'];
	      $this->expires = time() + $json['expires_in'];
      }

      /**
      * \fn getResources() request json data from the Fitbit API provider
      * \param uri uri to request
      * \param method optional parameter specifying the HTTP method to use
      * \param data optional parameter specifying the data to send
      * \return array of results
      *
      */
      private function getResources($uri, $method = 'GET', $data = null) {
      	      $this->refreshToken();
      	      $ch = curl_init();
      	      $options = array(CURLOPT_URL => $uri,
	      	       CURLOPT_RETURNTRANSFER => true,
		       CURLOPT_HTTPHEADER => array('Authorization: Bearer '.$this->accessToken));
              curl_setopt_array($ch, $options);
	      if ($method == 'POST')
	      	 curl_setopt($ch, CURLOPT_POST, true);
	      if ($data != null)
	      	 curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		 
	      return json_decode(curl_exec($ch), true);
      }

      /**
      * \fn getUserData() return current user profile
      * \return array with user profile data
      *
      */
      public function getUserProfile() {
	     return $this->getResources(HOST.'1/user/-/profile.json');
      }

      /**
      * \fn getHeartrateIntraday() return today user heartrate serie
      * \return array with today user heartrate serie
      *
      */
      public function getHeartrateIntraday() {
      	     return $this->getResources(HOST.'1/user/-/activities/heart/date/today/1d/1sec.json');
      }
}

?>