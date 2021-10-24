<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use  Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityConttrollerController extends AbstractController
{
    /**
     * @Route("/registration", name="security_registration")
     */
    public function index(Request $request, EntityManagerInterface $objectManager, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $pass_hash = $encoder->encodePassword($user, $user->getPassword() );
            $user->setPassword( $pass_hash );

            $objectManager->persist( $user );
            $objectManager->flush();

            $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", name="security_login")
     */
    public function login(){
        
        return $this->render('security/login.html.twig');
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout(){
    }
}
