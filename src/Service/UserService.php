<?php 

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class UserService{

        private $user;
        private $em;

        public function __construct(Security $security, EntityManagerInterface $em)
        {
            $this->user =  $security->getUser();
            $this->em = $em;
        }

        public function isAuth(){
            return $this->user;
        }

        public function setUser(User $user){
            $this->user = $user;
        }

           //  check if u can edit this post ?
        public function canIedit($post): bool{
            $author_post = $this->em->getRepository(Post::class)->find($post->getId());
            return $author_post->getUser() == $this->user;
        }
    }





?>