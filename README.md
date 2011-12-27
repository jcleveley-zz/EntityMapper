![Travis CI Status](https://secure.travis-ci.org/jcleveley/EntityMapper.png)

# Entity Mapper

The entity mapper is used to transform an array of data, usually from json_decode via a web service, to nested PHP objects.

The mapper knows what to map each array element to by providing an array map.

```php
<?php
$storyMeta = array(
    'title' => array('name' => 'title'),
    'contents' => array('name' => 'body'),
    'authors' => array('name' => 'authors'),
    'thumbnail' => array('name' => 'thumbnail', 'depth' => 0, 'class' => 'Image'),
    'images' => array('name' => 'images', 'depth' => 1, 'class' => 'Image'),
    'media' => array('name' => 'media', 'depth' => 2, 'class' => 'Image'),
    'relatedStory' => array('name' => 'relatedStory', 'class' => 'Story')
);

$imageMeta = array(
    'href' => array('name' => 'href'),
    'alt' => array('name' => 'alt'),
);

$this->map = array('Story' => $storyMeta, 'Image' => $imageMeta);
```