<?php 

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class UserService{

        private $user;

        public function __construct(Security $security)
        {
            $this->user =  $security->getUser();
        }

        public function isAuth(){
            return $this->user;
        }

        public function setUser(User $user){
            $this->user = $user;
        }
    }





?>