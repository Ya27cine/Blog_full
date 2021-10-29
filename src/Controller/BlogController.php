<?php 
 namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\PostType;
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
        
       
        // Get repository  Entity Post :
        $rep_post   = $this->getDoctrine()->getRepository(Post::class);

         // Get params  URL 
         $referred_Categ = $request->query->get('category', 'ALL');

        if( $referred_Categ != "ALL"){
            // get the id of the category indicated in the URL 
            $id_category = $this->getDoctrine()->getRepository(Category::class)->findBy(array('name' => $referred_Categ));

            // fetch  all articles belongoin to this category  ( $referred_Categ ):
            $posts = $rep_post->findBy(array('category' => $id_category ), array('published' => 'DESC'));
        }
        else
            // Get all articles
            $posts = $rep_post->findBy(array(), array('published' => 'DESC'));

          
        // prepare Pagination :
        $data = $paginator->paginate(
            $posts,
            $request->query->getInt('page', 1), // num de la page en cours, 1 par default
            4
        );

        // set Template pagina
        $data->setTemplate('pagination/bootstrap_v5_pagination.html.twig');

         /*
         * Count the number of articles by categories 
         * SQL :
         *  SELECT c.name , count(*) FROM `post` p, `category` c WHERE c.id = p.category_id GROUP BY c.name 
         */
        $em = $this->getDoctrine()->getManager();
        $categories = $em->createQuery("SELECT c.name ,  count(p.id) as occ FROM App\Entity\Post p, App\Entity\Category c WHERE c.id = p.category GROUP BY c.name")->getResult();


        

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

        $form = $this->createForm(PostType::class, $post);
             /*   ->add("title", TextType::class)
                ->add("content", TextareaType::class)
                ->add("create", SubmitType::class, ['label' => "New Post"])
                ->getForm();*/
        
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