![Travis CI Status](https://secure.travis-ci.org/jcleveley/EntityMapper.png)

# Entity Mapper

The entity mapper class is used to hydrate an array of data, usually from json_decode via a web service, to nested PHP objects.

To map the array to PHP objects you need to provide a map describing how the data should be transformed,

## Features

* Leaves your objects alone - no base clases etc
* Can hydrate nested objects and arrays

## Example

```php
<?php

require_once  __DIR__.'/../src/EntityMapper/Mapper.php';
require_once  __DIR__.'/Tweet.php';

use EntityMapper\mapper;

// Fetch the data
$contents = file_get_contents('http://search.twitter.com/search.json?q=london&rpp=5');
$tweetsContainer = json_decode($contents, true);


// Provide the mapping
$map = array(
    'Tweet' => array(
        'from_user_name' => array('name' => 'userName'),
        'created_at' => array('name' => 'createdAt', 'class' => 'DateTime')
    )
);

// Create the mapper - seting the mapper to automatically set properties of the same name
$mapper = new Mapper($map, true);

// Hydrate the twitter data to 'Tweet' objects at a depth of 1
$tweets = $mapper->hydrate($tweetsContainer['results'], 'Tweet', 1);

// Use the new objects
foreach ($tweets as $tweet) {
    echo "\n\n" . $tweet->getUserName() . ' - ' . $tweet->getCreatedAt() ;
    echo "\n ---> " . $tweet->getText();
}
```
### Result:
```
Daniel Chandra - Wednesday 28th of December 2011
 ---> RT @simplepassing: Cant wait for next (year) London derby, West Ham vs Chelsea :p RT @TruebluesIndo: *uhuk adminnya westham toh, ngeri juga kaya hooligan NYA

The Insider - Wednesday 28th of December 2011
 ---> MikeJonesWaPo: Lorenzo Alexander and London Fletcher both have been told they are alternates for the Pro Bowl. #REdskins

Frances Neaves - Wednesday 28th of December 2011
 ---> I want to keep those apron strings attached that little bit longer but admit she's growing up fast! Waiting for her to come home from London

Yuanyuan Fei - Wednesday 28th of December 2011
 ---> I'm at Santander (Harewood Place, London) http://t.co/cSxt0S3X

Gusti Albidi - Wednesday 28th of December 2011
 ---> Gondrong - london - paris - new york - denpasar - rajaampat - gondrong haha RT @rahmahanawati1: hhheheheh iya dch iya... lburan
```

### Advaced Usage

```php
<?php
// If you add a '_new' function to the map you can customise an object's creation.
// The function is passed the data and last string array key which is usefiul for deciding what object to create.
// So a complex array structure like array-Tweet-Entities-array['urls']-array-url can be hydrated pretty easily.

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
```