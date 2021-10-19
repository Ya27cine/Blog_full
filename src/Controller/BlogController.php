<?php 
 namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $rep   = $this->getDoctrine()->getRepository(Post::class);
        $posts = $rep->findAll();

        return $this->render('blog/index.html.twig', [
            'posts' => $posts
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
        if( $form->isSubmitted() && $form->isValid()){
           
            $post->setPublished(new \DateTime);
            $em->persist( $post);
            $em->flush();

            return $this->redirectToRoute('blog-index');
        }


         return $this->render('blog/create.html.twig', [
             'data' => 'New Post',
             'mForm' => $form->createView()
         ]);
      }



      /**
       * @Route("/prostam" , name="prostam-index", methods={"GET","POST"})
       */
      public function ajaxAction(Request $request){
            $students = $this->getDoctrine() 
                ->getRepository(Post::class) 
                ->findAll();  
                
            if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {  
                $jsonData = array();  
                $idx = 0;  
                foreach($students as $student) {  
                $temp = array(
                    'title' => $student->getTitle(),  
                    'content' => $student->getContent(),  
                );   
                $jsonData[$idx++] = $temp;  
                } 
                return new JsonResponse($jsonData); 
            } else { 
                return $this->json(['null']);
            } 
      }
 }
?>