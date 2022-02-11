<?php

namespace App\Event;

use Illuminate\Support\Facades\Event;

class CommentEvent extends Event{

    private $comment;
    private $author;
    public function __construct($comment, $author)
    {
        $this->comment = $comment;
        $this->author = $author;
    }

    public function getComment(){
        return $this->comment;
    }
    public function getAutor(){
        return $this->author;
    }
}



?>