<?php

class Tweet
{
    protected $userName;
    protected $text;
    protected $createdAt;
    protected $entities;


    public function getEntities()
    {
    	return $this->entities;
    }

    public function getHashes()
    {
        if (isset($this->entities['hashtags'])) {
            return $this->entities['hashtags'];
        } else {
            return array();
        }
    }

    public function getUserName()
    {
        return $this->userName;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getCreatedAt($format = 'l jS \of F Y ')
    {
    	if ($this->createdAt instanceof DateTime) {
        	return $this->createdAt->format($format);
    	}
    }

}