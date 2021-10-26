<?php

// You can find the keys here : https://developer.twitter.com/en/portal/projects-and-apps

$twitterConfig = require __DIR__ . '/../../vendor/atymic/twitter/config/twitter.php';

$twitterConfig['consumer_key'] = env('TWITTER_WIKIMEDIAMETER_CONSUMER_KEY');
$twitterConfig['consumer_secret'] = env('TWITTER_WIKIMEDIAMETER_CONSUMER_SECRET');
$twitterConfig['access_token'] = env('TWITTER_WIKIMEDIAMETER_ACCESS_TOKEN');
$twitterConfig['access_token_secret'] = env('TWITTER_WIKIMEDIAMETER_ACCESS_TOKEN_SECRET');

return $twitterConfig;
