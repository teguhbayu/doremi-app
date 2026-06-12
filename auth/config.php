<?php
$client = new Google_Client();
$client->setClientId($_ENV["GOOGLE_CLIENT_ID"]);
$client->setClientSecret($_ENV["GOOGLE_CLIENT_SECRET"]);
$client->setRedirectUri($_ENV["GOOGLE_CALLBACK_URL"]);

$client->addScope('email');
$client->addScope('profile');
