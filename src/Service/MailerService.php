<?php 
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService{

    /**
     * @var MailerInterface
     */
    protected $mailer;
    protected $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function sendMailier(){
        
        $email = (new Email())
        ->from('contact@blog.fr')
        ->to("email@example.fr")
        ->subject('You Order has been placed')
        ->html('<p> Thank you, your order has been placed</p>');

        $this->mailer->send($email);

        $this->logger->info("email envoye !");
    }
}


?>