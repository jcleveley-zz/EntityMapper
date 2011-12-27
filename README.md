![Travis CI Status](https://secure.travis-ci.org/jcleveley/EntityMapper.png)

# Entity Mapper

The entity mapper is used to transform an array of data, usually from json_decode via a web service, to nested PHP objects.

The mapper knows what to map each array element to by providing an array map.

```php
<?php
$map = array(
    'Story' =>array(
	    'title' => array('name' => 'title'),
	    'contents' => array('name' => 'body'),
	    'authors' => array('name' => 'authors'),
	    'images' => array('name' => 'images', 'depth' => 1, 'class' => 'Image'),
	    'relatedStory' => array('name' => 'relatedStory', 'class' => 'Story')
	),
	'Image' => array(
	    'href' => array('name' => 'href'),
	    'alt' => array('name' => 'alt')
	)
);
```