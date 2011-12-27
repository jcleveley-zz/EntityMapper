![Travis CI Status](https://secure.travis-ci.org/jcleveley/EntityMapper.png)

# Entity Mapper

The entity mapper is used to transform an array of data, usually from json_decode via a web service, to nested PHP objects.

The mapper knows what to map each array element to by providing an array map.

```php
<?php

// Map instructions ...

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

```php
// Target custom classes ...

class Story
{
    protected $title;
    protected $body;
    protected $thumbnail;
    protected $images;
    protected $authors;
    protected $media;
    protected $date;
    protected $relatedStory;

    // Getters and setters ...
}

class Image
{
    protected $href;
    protected $alt;

    // Getters and setters ...
}


```

```php
        $data = array(
            'title' => 'Once upon a time',
            'contents' => 'Here we go .... the end',
            'authors' => array('John', 'Frank'),
            'thumbnail' => array('href' => 'http://foo.com', 'alt' => 'nice pic'),
            'images' => array(array('href' => 'http://foo.com', 'alt' => 'nice pic')),
            'relatedStory' => array('title' => 'A title', 'body' => 'contents here')
        );
```        