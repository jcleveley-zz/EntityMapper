<?php

use EntityMapper\Mapper;

class MapperTest extends PHPUnit_Framework_TestCase
{

    public function setup()
    {
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

        $liveEventMeta = array(
            'channelId' => array('name' => 'channelId'),
        ) + $storyMeta;

        $this->map = array('Story' => $storyMeta, 'Image' => $imageMeta, 'LiveEvent' => $liveEventMeta);

        $this->data = array(
            'title' => 'Once upon a time',
            'contents' => 'Here we go .... the end',
            'authors' => array('John', 'Frank'),
            'thumbnail' => array('href' => 'http://foo.com', 'alt' => 'nice pic'),
            'images' => array(array('href' => 'http://foo.com', 'alt' => 'nice pic')),
            'media' => array(
                'bigImages' => array(array('href' => 'http://foo.com', 'alt' => 'nice pic')),
                'smallImages' => array(array('href' => 'http://foo.com', 'alt' => 'nice pic'))
            ),
        );

        $this->mapper = new Mapper($this->map);
    }

    public function testSimpleProperties()
    {
        $entity = $this->mapper->hydrate($this->data, 'Story');

        $this->assertEquals($this->data['title'], $entity->getTitle(), 'Should map plain properties');

        $this->assertEquals($this->data['contents'], $entity->getBody(), 'Should map renamed properties');

        $this->assertSame($this->data['authors'], $entity->getAuthors(), 'Should map arrays');
    }

    public function testAutoProperties()
    {
        $map['Story']['title'] = array('name' => 'title');
        $data['title'] = 'Esists in map';
        $data['body'] = 'not in map';

        $mapper = new Mapper($map, false);
        $entity = $mapper->hydrate($data, 'Story');

        $this->assertNull($entity->getBody(), 'Should ignore non-mapped data');
        $this->assertEquals('Esists in map', $entity->getTitle());

        $mapper = new Mapper($map, true);
        $entity = $mapper->hydrate($data, 'Story');

        $this->assertEquals('not in map', $entity->getBody(), 'Should set non-mapped properties');
        $this->assertEquals('Esists in map', $entity->getTitle());
    }

    public function testObjectCreation()
    {
        $entity = $this->mapper->hydrate($this->data, 'Story');

        //test depth 0
        $this->AssertTrue($entity->getThumbnail() instanceof Image, 'Should map to custom oject');
        $this->assertSame(
            $this->data['thumbnail']['alt'],
            $entity->getThumbnail()->getAlt(),
            'Should map child properties'
        );
    }

    public function testNestedObjectCreation()
    {
        $entity = $this->mapper->hydrate($this->data, 'Story');

        //test depth 1
        $this->AssertTrue(is_array($entity->getImages()), 'Should create an array of objects');
        $this->AssertTrue(
            array_pop($images = $entity->getImages()) instanceof Image,
            'Should create nested object of right class'
        );

        // test depth 2
        $this->assertTrue(is_array($entity->getMedia()));
        $media = $entity->getMedia();
        $this->assertTrue(is_array($media['bigImages']));
        $this->assertTrue($media['bigImages'][0] instanceof Image, 'Should map deep arays with class at end');
    }

    public function testCustomOjectCreation()
    {
        $this->map['Story']['_new'] = function($data) {
            if (@$data['type'] == 'LEP') {
                return new LiveEvent();
            } else {
                return new Story();
            }
        };
        $this->data['relatedStory'] = array('type' => 'LEP', 'channelId' => 'ash-cloud');
        $this->mapper = new Mapper($this->map);

        $entity = $this->mapper->hydrate($this->data, 'Story');
        $this->assertTrue($entity->getRelatedStory() instanceof LiveEvent, 'Should call _new if present');
        $this->assertEquals(
            'ash-cloud',
            $entity->getRelatedStory()->getChannelId(),
            'Should use class map of object returned in _new'
        );
    }

    public function testBadCallableCreation()
    {
        $this->map['Story']['_new'] = 'this is not callable!';
        $this->mapper = new Mapper($this->map);

        $entity = $this->mapper->hydrate($this->data, 'Story');

        $this->assertTrue($entity instanceof Story, 'Should silently fail if _new is not callable');
    }

    public function testNativeObjectCreation()
    {
        $this->map['Story']['date'] = array('name' => 'date', 'class' => 'DateTime');
        $this->data['date'] = '2011-11-07T14:40:40+00:00';
        $this->mapper = new Mapper($this->map);

        $entity = $this->mapper->hydrate($this->data, 'Story');

        $this->assertTrue($entity->getDate() instanceof DateTime, 'Should map to native objects');
        $this->assertEquals('1320676840', $entity->getDate()->getTimestamp(), 'Should construct native correctly');
    }

    public function testNativeObjectException()
    {
        $this->map['Story']['date'] = array('name' => 'date', 'class' => 'DateTime');
        $this->data['date'] = 'thisisnotadate';
        $this->mapper = new Mapper($this->map);

        $entity = $this->mapper->hydrate($this->data, 'Story');
        $this->assertNull($entity->getDate());
    }    


}

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

    public function getTitle()
    {
        return $this->title;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function getAuthors()
    {
        return $this->authors;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getRelatedStory()
    {
        return $this->relatedStory;
    }
}

class LiveEvent extends Story
{
    protected $channelId;

    public function getChannelId()
    {
        return $this->channelId;
    }
}

class Image
{
    protected $href;
    protected $alt;

    public function getAlt()
    {
        return $this->alt;
    }

}