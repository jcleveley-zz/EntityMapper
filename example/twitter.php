#!/usr/bin/php
<?php

require_once  __DIR__.'/../src/EntityMapper/Mapper.php';
require_once  __DIR__.'/Tweet.php';

use EntityMapper\mapper;

$contents = file_get_contents(
	'http://search.twitter.com/search.json?q=blue%20angels&rpp=5&include_entities=true&result_type=mixed'
);

$tweetsContainer = json_decode($contents, true);

$creatEntity = function($data, $lastStringKey) {
	switch ($lastStringKey) {
		case 'hashtags':
			return new Hash;
		case 'urls':
			return new Url;
	}
};

$map = array(
	'Tweet' => array(
		'from_user_name' => array('name' => 'userName'),
		'created_at' => array('name' => 'createdAt', 'class' => 'DateTime'),
		'entities'=> array('name' => 'entities', 'class' => 'Entity', 'depth' => 2)
	),
	'Entity' => array('_new' => $creatEntity)
);


$mapper = new Mapper($map, true);

$tweets = $mapper->hydrate($tweetsContainer['results'], 'Tweet', 1);

foreach ($tweets as $tweet) {
	echo "\n\n" . $tweet->getUserName() . ' - ' . $tweet->getCreatedAt() ;
	echo "\n ---> " . $tweet->getText();
	foreach ($tweet->getHashes() as $key => $hash) {
		echo "\nHash $key: $hash->text";
	}

}

class Entity
{
	protected $indices;
}

class Hash extends Entity
{
	public $text; // im being lazy :)
}

class Url extends Entity
{
	protected $url;
}