<?php

namespace App\Subscriber;

use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EasyAdminSubscriber implements EventSubscriberInterface{

    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    
   public static  function  getSubscribedEvents()
   {
        return[
            BeforeEntityPersistedEvent::class => ['setBlogPostEncodePassword']
        ];
   }

   public function setBlogPostEncodePassword(BeforeEntityPersistedEvent $e){

    $entity = $e->getEntityInstance();

    if( ! ($entity instanceof User ) ){
        return;
    }

    $entity->setPassword( $this->encoder->encodePassword($entity, $entity->getPassword() ) );



   }// end fct setBlog...

}// end class EasyAdm...
?>