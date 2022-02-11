<?php 

namespace App\EventDispatcher;

use App\Entity\User;
use App\Event\ConstantsEvent;
use App\Event\MembershipRegistrationEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserRegistrationSubscriber  implements EventSubscriberInterface {

    /**
     * @var MailerInterface
     */
    private $mailer;

    private $urlGeneratorInterface;

    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $urlGeneratorInterface)
    {
        $this->mailer = $mailer;
        $this->urlGeneratorInterface = $urlGeneratorInterface;
    }


    public static function getSubscribedEvents()
    {
        return[
            ConstantsEvent::USER_AFTER_REGISTRATION =>  "sendEmailAfterRegistr"
        ];
    }

    public function sendEmailAfterRegistr(MembershipRegistrationEvent $_user){

        // generate url for create first article for client
        $new_article_url = $this->urlGeneratorInterface->generate('blog-create',
        [],
        UrlGeneratorInterface::ABSOLUTE_URL);

        /**
         * @var User
         */
        $user =  $_user->getUser();
         
        $email = (new TemplatedEmail())
        ->from('contact@blog.fr')
        ->to($user->getEmail())
        ->subject('Rigstration in MyBlog')
        ->htmlTemplate('emails/registration_view.html.twig')
        ->context([
            'user' => $user,
            'new_article' => $new_article_url
        ]);
        $this->mailer->send($email);

        

        

    }


}


?>