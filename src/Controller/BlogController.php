<?php 
 namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Postlike;
use App\Event\CommentEvent;
use App\Event\ConstantsEvent;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\PostlikeRepository;
use App\Service\BlogService;
use App\Service\FileUploaderService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
 {

     /**
      * @Route("/", name="blog-index")
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


        // set Template paginator_posts
        $paginator_posts->setTemplate('pagination/bootstrap_v5_pagination.html.twig');
         
         // Count the number of articles by categories 
        $categories = $blogService->countPotsByCategories();
        
        // count the number of articles in all categories :
        $post_sum = $blogService->countPosts();


        // Get list Posts Interactive (have more Comments ..)
        $posts_most_comments =  $blogService->getIdpostsInteractiveByComments();

        // Get list Posts Interactive (have more likes ..)
        $posts_most_likes =  $blogService->getIdpostsInteractiveByLikes();


        return $this->render('blog/index.html.twig', [
            'posts' => $paginator_posts,
            'categories' => $categories,
            'posts_sum' => $post_sum,
            'posts_most_comments' => $posts_most_comments,
            'posts_most_likes' => $posts_most_likes

        ]);
     }


      /**
      * @Route("/post/{id}", name="blog-show", requirements={ "id" = "\d+" } )
      */
      public function show(Post $post=null, Request $request, EntityManagerInterface $em, UserService $userService,
      EventDispatcherInterface $dispatcher)
      { 
        if( ! isset($post) ) // page 404
            return $this->redirectToRoute('blog-index');

         // get User auth
         $user = $userService->isAuth();
         if(! $user ){ // show only post , without form add comment
            return $this->render('blog/show.html.twig', 
            [ 'post' => $post, 'comment_form' => null]);
         }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() ){

            // Persist Comment :
            $comment->setCreatedAt(new \DateTime() );
            $comment->setAuthor( $user );
            $comment->setPost( $post );
            $em->persist($comment);
            $em->flush();

            // send notif to write for this article:
            // 1: check if there it is my article:
            // if not my post, i will send email to writer  
            if( $post->getUser()->getId() != $user->getId() )
            {
                $dispatcher->dispatch(new CommentEvent($comment, $post->getUser() ),
                     ConstantsEvent::POST_NEW_COMMENT );
            }

            return $this->redirectToRoute('blog-show', ['id' => $post->getId() ] );
        }


        return $this->render('blog/show.html.twig', [
             'post' => $post,
             'comment_form' => $form->createView()
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

         //$dispather->dispatch(new GenericEvent($post), "post.delete");

         $em->flush();
 
         return $this->redirectToRoute('blog-my-posts');
      }


    /**
      * @Route("/blog/profil", name="blog-my-posts")
      */
      public function profil(PaginatorInterface $paginator, Request $request,EventDispatcherInterface $dispatcher , BlogService $blogService, UserService $userService)
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

            $paginator_my_posts = $paginator->paginate(
                $my_posts,
                $request->query->getInt('page', 1), // num de la page en cours, 1 par default
                3
            );
            // set Template paginator_posts
            $paginator_my_posts->setTemplate('pagination/bootstrap_v5_pagination.html.twig');
         
            // Count the number of posts by categories 
            $occ_my_post_by_categ = $blogService->countMyPotsByCategories( $user );

            // count the number of articles in all categories :
            $count_my_posts = $blogService->countMyPosts( $user );

        } catch (\Throwable $th) {
             die($th);
        }
 
         return $this->render('blog/profil.html.twig', [
             'posts' => $paginator_my_posts,
             'categories' => $occ_my_post_by_categ,
             'count_my_posts' => $count_my_posts,
         ]);
      }




     /**
      * @Route("/blog/create", name="blog-create")
      */
      public function create(Request $request, EntityManagerInterface $em, UserService $userService, FileUploaderService $fileUploaderService)
      {
        $user  = $userService->isAuth();
        if(! $user)
            return $this->redirectToRoute('security-login');

        $post = new Post;

        $form = $this->createForm(PostType::class, $post);
        
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid()){
           
            $post->setPublished(new \DateTime);
            $post->setUser($user);

            //===== Upload Image 
            $imageFile = $form->get('image')->getData();
            if($imageFile){
                $newFilename = $fileUploaderService->upload($imageFile);
                $post->setImage($newFilename);
            }else{
                $post->setImage('default-img.jpg');
            }
            //====================

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
      * @Route("/blog/post/{id}/edit", name="blog-update", requirements={ "id" = "\d+" })
      */
      public function update(Post $post=null, Request $request, UserService $userService, EntityManagerInterface $em, FileUploaderService $fileUploaderService)  
      {
        $user  = $userService->isAuth();
        if(! $user)
            return $this->redirectToRoute('security-login');

        if( ! isset($post) ) 
             return $this->redirectToRoute('blog-index');

        //  check if u can edit this post ?
        if( ! $userService->canIedit( $post )) // page 404
                return $this->redirectToRoute('blog-index');

        // if user not update image, we will again save same name    
        $copy_nameFile = $post->getImage();

        // we must transform the image string from Db  to File to respect the form types   
        try {
            $old_file =  $fileUploaderService->getFileImage( $post->getImage() );
            $post->setImage($old_file);

        } catch (\Throwable $th) {
           // $post->setImage(  );
        }

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest( $request );

        if( $form->isSubmitted() && $form->isValid()){

            //====== Upload Image : =============
            $imageFile = $form->get('image')->getData();
            if($imageFile){
                $newFilename = $fileUploaderService->upload( $imageFile );
                $post->setImage($newFilename);
            }else{
                $post->setImage( $copy_nameFile );
            }
            //=====================================

            $em->persist( $post);
            $em->flush();

            return $this->redirectToRoute('blog-show', ['id' => $post->getId() ]);
        }

         return $this->render('blog/edit.html.twig', [
             'data' => 'Update Post',
             'mForm' => $form->createView(),
             'image' => $copy_nameFile,
             'user' => $user,
             'post_id' => $post->getId()
         ]);
      }


      /**
       * @Route("/blog/{id}/like" , name="post-like", requirements={ "id" = "\d+" }, methods={"GET","POST"})
       */
      public function makeLike(Post $post, PostlikeRepository $postlikeRepository, UserService $userService, EntityManagerInterface $em): Response{

        $user  = $userService->isAuth();
        if(! $user)
            return $this->json([
                'code' => 403,
                'message' => "non autorise"
            ], 403);
        
            if($post->islikebyUser($user)){

                $like = $postlikeRepository->findOneBy([
                    'post' => $post,
                    'user' => $user
                ]);
                $em->remove($like);           
                $em->flush();

                return $this->json([
                    'code' => 200,
                    'message' => "like is deleted",
                    'likes' => $postlikeRepository->count(['post'=> $post]),
                    'post' => $post->getId()
                ], 200);

            }else{

                $like = new Postlike();
                $like->setPost($post)
                     ->setUser($user);
                $em->persist($like);
                $em->flush();

                return $this->json([
                    'code' => 200,
                    'message' => "like is added",
                    'likes' => $postlikeRepository->count(['post'=> $post]),
                    'post' => $post->getId()
                ], 200);

            }
      }

 }
?>