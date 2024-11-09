<?php
// Get the PHP helper library from https://twilio.com/docs/libraries/php

// this line loads the library 
require_once 'vendor/autoload.php';
use Twilio\TwiML\VoiceResponse;

$response = new VoiceResponse;
$response->say("Hello world!");
print $response;
