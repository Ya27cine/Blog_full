<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $faker;
    private $passwordEncoder;
    private const ROWS_POST = 17;
    private const ROWS_COMMENT = 4;
    private const ROWS_USER = 5;

    function __construct(UserPasswordEncoderInterface $userPasswordEncoderInterface)
    {
        $this->passwordEncoder = $userPasswordEncoderInterface;
        $this->faker = Factory::create('fr_FR');
    }


    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        

        $categories = [
            ['name' => 'FrontEnd', 'count' => 5] ,
            ['name' => 'BackEnd',  'count' => 7] ,
            ['name' => 'FullStak', 'count' => 2] ,
            ['name' => 'Mobile',   'count' => 11] ,
            ['name' => 'Security', 'count' => 4] 
        ];

       foreach ($categories as $category) {

           $n_category = new Category();
           $n_category->setName(  $category['name'] );
           $manager->persist($n_category);

            // Create User :
            for ($i=0; $i < self::ROWS_USER; $i++) { 
                $user = new User();

                $user->setUsername( $this->faker->userName );
                $user->setEmail( $this->faker->email );

                $hash = $this->passwordEncoder->encodePassword($user, "12345678");
                $user->setPassword( $hash );
                
                $user->settRoles(['ROLE_USER']);

                $manager->persist($user);
                 // Create Posts :
                for ($i=0; $i < rand(1, self::ROWS_POST); $i++) { 
                    $post = new Post();

                    $post->setTitle( implode(" ",$this->faker->words()) );
                    $post->setContent(  implode(" ",$this->faker->words(700)) );
                    $post->setPublished( $this->faker->dateTimeBetween("-100 days") );
                    $post->setCategory( $n_category );
                    $post->setImage('6182fa127f10d.jpeg');
                    $post->setUser($user);

                    $manager->persist($post);
                        // Create Comments :
                        for ($k=0; $k < rand(0,5); $k++) { 
                            $comment = new Comment;

                            $comment->setContent(  implode(" ",$this->faker->words(100)) );
                            $date_post = $post->getPublished()->format('d');
                            $comment->setCreatedAt( $this->faker->dateTimeBetween("-".$date_post." days") );
                            $comment->setPost( $post );
                            $comment->setAuthor($user);

                            $manager->persist($comment);              
                        }// end: comment
                }// end: post
            }// end: user
       }// end: category


        $manager->flush();
    }
}
