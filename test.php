<?php

require_once 'GrantType/IGrantType.php';
require_once 'GrantType/AuthorizationCode.php';
require_once 'Client.php';

//get these values from the FreeAgent developer dashboard
$identifier = '';
$secret = '';

//the URL of this script. doesn't have to be publicly accessible.
$script_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];

//the base URL of the API. shouldn't need to change this.
$base_url = 'https://api.freeagent.com/v2';

//create the OAuth client
$client = new OAuth2\Client($identifier, $secret);

//check what stage we're at
if (empty($_GET['code']) && empty($_GET['token'])) {

	//no code and no token so redirect user to FreeAgent to log in
	$auth_url = $client->getAuthenticationUrl($base_url . '/approve_app', $script_url);
	header('Location: ' . $auth_url);

} elseif (isset($_GET['code'])) {

	//we have a code so use it to get an access token
	$response = $client->getAccessToken(
		$base_url . '/token_endpoint',
		'authorization_code',
		array('code' => $_GET['code'], 'redirect_uri' => $script_url)
	);

	//normally you would store the token for use in future requests
	$token = $response['result']['access_token'];
	header('Location: ' . $script_url . '?token=' . $token);

} elseif (isset($_GET['token'])) {

	//when we have a token, just set up the client
	$client->setAccessToken($_GET['token']);
	$client->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_BEARER);

	//and make the request to the API
	$response = $client->fetch(
		$base_url . '/projects', //API path
		array(), //request parameters
		OAuth2\Client::HTTP_METHOD_GET, //GET, PUT, POST, DELETE
		array('User-Agent' => 'Example app') //API requires UA header
	);

	//show response
	echo '<pre>'.print_r($response, true).'</pre>';
}
