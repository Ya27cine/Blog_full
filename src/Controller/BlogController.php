<?php 
 namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Service\BlogService;
use App\Service\UserService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
 {
    public function __construct(){}

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
     public function index(PaginatorInterface $paginator, Request $request, BlogService $blogService)
     {
        // posts 
        $posts = null;

         // Get params  URL 
         $referred_name_categ = $request->query->get('category', 'ALL');

         // get all posts
        $posts = $blogService->posts($referred_name_categ);
               
        // prepare Pagination :
        $paginator_posts = $paginator->paginate(
            $posts,
            $request->query->getInt('page', 1), // num de la page en cours, 1 par default
            7
        );

        // set Template pagina
        $paginator_posts->setTemplate('pagination/bootstrap_v5_pagination.html.twig');
         
         // Count the number of articles by categories 
        $categories = $blogService->countPotsByCategories();
        
        // count the number of articles in all categories :
        $post_sum = $blogService->countPosts();

        return $this->render('blog/index.html.twig', [
            'posts' => $paginator_posts,
            'categories' => $categories,
            'posts_sum' => $post_sum
        ]);
     }



      /**
      * @Route("/blog/post/{id}", name="blog-show", requirements={ "id" = "\d+" } )
      */
      public function show(Post $post=null)
      { 
        if( ! isset($post) ) // page 404
            return $this->redirectToRoute('blog-index');

        return $this->render('blog/show.html.twig', [
             'post' => $post
         ]);
      }




     /**
      * @Route("/blog/{id}/delete", name="blog-delete", requirements={ "id" = "\d+" } )
      */
      public function delete($id)
      {
         $post   = $this->getDoctrine()->getRepository(Post::class)->find($id);
         $em    =  $this->getDoctrine()->getManager();
         $em->remove($post);

         $em->flush();
 
         return $this->redirectToRoute('blog-my-posts');
      }





    /**
      * @Route("/blog/my-posts", name="blog-my-posts")
      */
      public function myposts(Request $request, BlogService $blogService, UserService $userService)
      {
        try{
            // get User auth
            $user = $userService->isAuth();
            // check if a user is login
            if(! $user )
                return $this->redirectToRoute('security_login');

            // My posts :
            $my_posts = null;

            // Get params  URL 
            $referred_name_categ = $request->query->get('category', 'ALL'); // take All By default
            
            // fetch  all posts / Or belongoin to this category  ( $referred_name_categ ):
            $my_posts = $blogService->posts( $referred_name_categ, $user);
         
            // Count the number of posts by categories 
            $occ_my_post_by_categ = $blogService->countMyPotsByCategories( $user );

            // count the number of articles in all categories :
            $count_my_posts = $blogService->countMyPosts( $user );

        } catch (\Throwable $th) {
             die($th);
        }
 
         return $this->render('blog/my-list.html.twig', [
             'posts' => $my_posts,
             'categories' => $occ_my_post_by_categ,
             'count_my_posts' => $count_my_posts
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
      * @Route("/blog/{id}/update", name="blog-update", requirements={ "id" = "\d+" })
      */
      public function update(Post $post=null, Request $request)  
      {
        $em   = $this->getDoctrine()->getManager();
        $user  = $this->security->getUser();
        if(! $user)
            return $this->redirectToRoute('security-login');

        if( ! isset($post) ) 
             return $this->redirectToRoute('blog-index');

        $form = $this->createForm(PostType::class, $post);
        
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid()){
    
            $em->persist( $post);
            $em->flush();

            return $this->redirectToRoute('blog-show', ['id' => $post->getId() ]);
        }

         return $this->render('blog/update.html.twig', [
             'data' => 'Update Post',
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