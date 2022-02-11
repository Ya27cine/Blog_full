<?php  

namespace App\EventDispatcher;

use App\Event\CommentEvent;
use App\Event\ConstantsEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class PostNewCommentEvent implements EventSubscriberInterface{

     /**
     * @var MailerInterface
     */
    private $mailer;
    private $sender_email;

    public function __construct(MailerInterface $mailer , $senderEmail)
    {
        $this->mailer = $mailer;
        $this->sender_email = $senderEmail;
    }


    public static function getSubscribedEvents()
    {
        return[
            ConstantsEvent::POST_NEW_COMMENT =>  "sendEmailAfterRegistr"
        ];
    }

    public function sendEmailAfterRegistr(CommentEvent $commentEvent){

        $comment =  $commentEvent->getComment();
        $author  =  $commentEvent->getAutor();

        $email = (new TemplatedEmail())
        ->from( $this->sender_email )
        ->to($author->getEmail())
        ->subject('event comment')
        ->htmlTemplate('emails/comment_view.html.twig')
        ->context([
            'comment' => $comment,
            'author' => $author
        ]);
        $this->mailer->send($email);

    }


}



?>