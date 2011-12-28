![Travis CI Status](https://secure.travis-ci.org/jcleveley/EntityMapper.png)

# Entity Mapper

The entity mapper class is used to hydrate an array of data, usually from json_decode via a web service, to nested PHP objects.

To map the array to PHP objects you need to provide a map describing how the data should be transformed,

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