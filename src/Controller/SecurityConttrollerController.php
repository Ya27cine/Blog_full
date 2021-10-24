<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use  Doctrine\ORM\EntityManagerInterface;

class SecurityConttrollerController extends AbstractController
{
    /**
     * @Route("/registration", name="security_registration")
     */
    public function index(Request $request, EntityManagerInterface $objectManager)
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $objectManager->persist( $user );
            $objectManager->flush();
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
