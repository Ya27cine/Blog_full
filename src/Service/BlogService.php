<?php 

    namespace App\Service;

use App\Entity\User;
use App\Repository\PostRepository;
use Symfony\Component\Security\Core\Security;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class BlogService{

        protected $postRepository;
        protected $categoryRepository;
        protected $security;
        protected $entityManager;
        public $user;



        public function __construct(Security $security,PostRepository $postRepository, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager)
        {
            $this->postRepository     = $postRepository;
            $this->categoryRepository = $categoryRepository;
            $this->entityManager      = $entityManager;
            $this->security           = $security;
            $this->user               =  $this->security->getUser();
        }

        public function isAuth(){
            return $this->user;
         }

        public function getPostsByCategory(User $user, $id_category, $orderBy = "DESC"){
            if(! $user) // all posts by category:id , page index
                return $this->postRepository->findBy(array('category' => $id_category ), array('published' => $orderBy));
            else // my posts by category:id
                return  $this->postRepository->findBy(array('user' => $user->getId(),'category' => $id_category ), array('published' => $orderBy));
        }

        public function getPosts(User $user, $orderBy = "DESC"){
            if(! $user) // all posts, page index
                return $this->postRepository->findBy(array(), array('published' => $orderBy));
            else // my posts 
                return  $this->postRepository->findBy(array('user' => $user->getId() ), array('published' => $orderBy));
        }

        public function getIdCategoryByName($name){
            return $this->categoryRepository->findBy(array('name' => $name));
        }

        public function countMyPotsByCategories(){
           /*
            * Count the number of articles by categories 
            * SQL :: SELECT c.name , count(*) FROM `post` p, `category` c WHERE c.id = p.category_id GROUP BY c.name 
            * return [ 
                        {obj:category, count 'occ' }
                        ...
                    ]
            */
            return
               $this->entityManager->createQuery("SELECT c.name ,count(p.id) as occ FROM App\Entity\Post p, App\Entity\Category c WHERE p.user = ".$this->user->getId()." and c.id = p.category GROUP BY c.name")->getResult();
            
        }

        public function countMyPosts($user){
            return  count( $this->postRepository->findBy(['user' => $user ]) );
        }


    }
?>