<?php 
 namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

 class BlogController extends AbstractController
 {
     /**
      * @Route("/blog", name="blog-index")
      */
     public function index()
     {
        return $this->render('blog/index.html.twig', [
            'data' => 'all-posts'
        ]);
     }

     /**
      * @Route("/blog/create", name="blog-create")
      */
      public function create()
      {
         return $this->render('blog/create.html.twig', [
             'data' => 'New Post'
         ]);
      }
 }
?>