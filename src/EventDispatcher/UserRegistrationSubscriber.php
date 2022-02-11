<?php 

namespace App\EventDispatcher;

use App\Entity\User;
use App\Event\ConstantsEvent;
use App\Event\MembershipRegistrationEvent;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class UserRegistrationSubscriber  implements EventSubscriberInterface {

    /**
     * @var MailerInterface
     */
    private $mailer;
    private $logger;
    private $urlGeneratorInterface;
    private $sender_email;

    public function __construct(MailerInterface $mailer, $senderEmail,LoggerInterface $logger, UrlGeneratorInterface $urlGeneratorInterface)
    {
        $this->mailer = $mailer;
        $this->urlGeneratorInterface = $urlGeneratorInterface;
        $this->sender_email = $senderEmail;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return[
            ConstantsEvent::USER_AFTER_REGISTRATION =>  "sendEmailAfterRegistr"
        ];
    }

    public function sendEmailAfterRegistr(MembershipRegistrationEvent $_user){

        try {
                // generate url for create first article for client
                $new_article_url = $this->urlGeneratorInterface->generate('blog-create',
                [ ], UrlGeneratorInterface::ABSOLUTE_URL);
                /**
                 * @var User
                 */
                $user =  $_user->getUser();
                
                $email = (new TemplatedEmail())
                ->from($this->sender_email)
                ->to($user->getEmail())
                ->subject('Rigstration in MyBlog')
                ->htmlTemplate('emails/registration_view.html.twig')
                ->context([
                    'user' => $user,
                    'new_article' => $new_article_url
                ]);
                $this->mailer->send($email);

                $this->logger->info("EventSubscriber:: sendEmailAfterRegistr user : ".$user->getUsername());
        } catch (Exception $e) {
            $this->logger->error("cant send email event:sendEmailAfterRegistr!! ");
        }
       
    }
}


?>