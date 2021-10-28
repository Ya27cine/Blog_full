<?php 
 namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use Knp\Component\Pager\PaginatorInterface;
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
     public function index(PaginatorInterface $paginator, Request $request)
     {
         // get para URL 
        $nameCateg = $request->query->get('category', 'ALL');

        // get All categories 
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        $rep   = $this->getDoctrine()->getRepository(Post::class);

        if( $nameCateg != "ALL"){
            // get id category 
            $id_category = $this->getDoctrine()->getRepository(Category::class)->findBy(array('name' => $nameCateg));
            // get post by category :
            $posts = $rep->findBy(array('category' => $id_category ), array('published' => 'DESC'));
        }
        else
            // get all posts
            $posts = $rep->findBy(array(), array('published' => 'DESC'));

        // prepare Pagination :
        $data = $paginator->paginate(
            $posts,
            $request->query->getInt('page', 1), // num de la page en cours, 1 par default
            4
        );

        // set Template pagina
        $data->setTemplate('pagination/bootstrap_v5_pagination.html.twig');
        
        
        return $this->render('blog/index.html.twig', [
            'posts' => $data,
            'categories' => $categories
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
            return $this->redirectToRoute('security_login');

         $rep   = $this->getDoctrine()->getRepository(Post::class);

         $posts = $rep->findBy( ['user' => $user->getId() ], array('published' => 'ASC') );
 
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