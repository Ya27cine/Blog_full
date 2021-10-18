<?php 
 namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
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
      public function create(Request $request)
      {
        $em   = $this->getDoctrine()->getManager();
        $post = new Post;

        $form = $this->createFormBuilder( $post )
                ->add("title", TextType::class)
                ->add("content", TextareaType::class)
                ->add("create", SubmitType::class, ['label' => "New Post"])
                ->getForm();
        
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid() && 0 ){
           
            $em->persist( $post);
            $em->flush();

            return $this->redirectToRoute('blog-create');
        }


         return $this->render('blog/create.html.twig', [
             'data' => 'New Post',
             'mForm' => $form->createView()
         ]);
      }
 }
?>