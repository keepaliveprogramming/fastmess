<?php
    namespace App\Http\Controllers;

    use Illuminate\Support\Facades\Mail;
    use Swift_Mailer;
    use Swift_Message;
    use Swift_SmtpTransport;

    class EmailSender extends Controller
    {
        protected $transport;

        public function __construct()
        {
            //$this->configureTransport();
        }

        public function sendEmail($from, $to, $subject, $content)
        {
            
            Mail::getSwiftMailer()->setDefaultTransport($this->configureTransport());
            //Mail::getSwiftMailer()->setDefaultTransport($this->configureTransport()));
            Mail::send('d', 'd', function ($message) use ($from, $to, $subject, $content) {
                $message->from($from)->to($to)->subject($subject)->setBody($content);
            });
            //return $this->configureTransport();
        }

        protected function configureTransport()
        {
            $con = new Swift_SmtpTransport('s1.ct8.pl', 25);
            $con->setUsername('[login]');
            $con->setPassword('[haslo]');
            return $con;
        }
    }

?>
