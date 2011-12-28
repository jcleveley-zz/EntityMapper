#!/usr/bin/php
<?php

require_once  __DIR__.'/../src/EntityMapper/Mapper.php';
require_once  __DIR__.'/Tweet.php';

use EntityMapper\mapper;

$contents = file_get_contents('http://search.twitter.com/search.json?q=london&rpp=5');
$tweetsContainer = json_decode($contents, true);

$map = array(
	'Tweet' => array(
		'from_user_name' => array('name' => 'userName'),
		'created_at' => array('name' => 'createdAt', 'class' => 'DateTime')
	)
);

$mapper = new Mapper($map, true);

$tweets = $mapper->hydrate($tweetsContainer['results'], 'Tweet', 1);

foreach ($tweets as $tweet) {
	echo "\n\n" . $tweet->getUserName() . ' - ' . $tweet->getCreatedAt() ;
	echo "\n ---> " . $tweet->getText();
}