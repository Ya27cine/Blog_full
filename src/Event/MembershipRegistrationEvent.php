<?php

namespace App\Event;

use Illuminate\Support\Facades\Event;

class MembershipRegistrationEvent extends Event{

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function getUser(){
        return $this->user;
    }
}

?>