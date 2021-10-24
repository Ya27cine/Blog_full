<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityConttrollerController extends AbstractController
{
    /**
     * @Route("/registration", name="security_registration")
     */
    public function index(): Response
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
