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
use Symfony\Component\Security\Core\Security;

class BlogController extends AbstractController
 {

    private $security;

    public function __construct(Security $s)
    {
        $this->security = $s;
    }

     /**
      * @Route("/", name="blog-default")
      */
      public function toblog()
      { 
         return $this->redirectToRoute('blog-index');
      }

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
      * @Route("/blog/post/{id}", name="blog-show", requirements={ "id" = "\d+" } )
      */
      public function show($id)
      {
         $rep   = $this->getDoctrine()->getRepository(Post::class);
         $post = $rep->find($id);
 
         return $this->render('blog/show.html.twig', [
             'post' => $post
         ]);
      }

       /**
      * @Route("/blog/my-posts", name="blog-my-posts")
      */
      public function myposts()
      {
        $user  = $this->security->getUser();
        if(! $user)
            return $this->redirectToRoute('security-login');

         $rep   = $this->getDoctrine()->getRepository(Post::class);

         $posts = $rep->findBy( ['user' => $user->getId() ] );
 
         return $this->render('blog/my-list.html.twig', [
             'posts' => $posts
         ]);
      }

     /**
      * @Route("/blog/create", name="blog-create")
      */
      public function create(Request $request)
      {
        $em   = $this->getDoctrine()->getManager();
        $user  = $this->security->getUser();
        if(! $user)
            return $this->redirectToRoute('security-login');


        $post = new Post;

        $form = $this->createFormBuilder( $post )
                ->add("title", TextType::class)
                ->add("content", TextareaType::class)
                ->add("create", SubmitType::class, ['label' => "New Post"])
                ->getForm();
        
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid()){
           
            $post->setPublished(new \DateTime);
            $post->setUser($user);

            $em->persist( $post);
            $em->flush();

            return $this->redirectToRoute('blog-index');
        }


         return $this->render('blog/create.html.twig', [
             'data' => 'New Post',
             'mForm' => $form->createView(),
             'user' => $user
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