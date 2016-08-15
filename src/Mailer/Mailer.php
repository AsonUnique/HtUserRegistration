<?php
namespace HtUserRegistration\Mailer;

use HtUserRegistration\Entity\UserRegistrationInterface;
use HtUserRegistration\Options\ModuleOptions;
use MtMail\Service\Mail as MailService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class Mailer implements MailerInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * Constructor
     *
     * @param ModuleOptions $options
     * @param MailService $mailService
     */
    public function __construct(ModuleOptions $options, MailService $mailService)
    {
        $this->options      = $options;
        $this->mailService  = $mailService;
    }

    /**
     * {@inheritDoc}
     */
    public function sendVerificationEmail(UserRegistrationInterface $registrationRecord)
    {
        $this->sendMail(
            $registrationRecord, 
            $this->options->getVerificationEmailSubject(), 
            $this->options->getVerificationEmailTemplate()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function sendPasswordRequestEmail(UserRegistrationInterface $registrationRecord)
    {
        $this->sendMail(
            $registrationRecord, 
            $this->options->getPasswordRequestEmailSubject(), 
            $this->options->getPasswordRequestEmailTemplate()
        );        
    }

    /**
     * Sends mail
     *
     * @param UserRegistrationInterface $registrationRecord
     * @param string $subject
     * @param string $template
     */
    protected function sendMail(UserRegistrationInterface $registrationRecord, $subject, $template)
    {
        $user = $registrationRecord->getUser();
        $message = $this->mailService->compose(
            ['to' => $user->getEmail()], 
            $template, 
            ['user' => $user, 'registrationRecord' => $registrationRecord]
        );

        $fromEmail = $this->options->getEmailFromAddress();
        if ($fromEmail) {
            $message->setFrom($fromEmail);
        }

        $translate = $this->getServiceLocator()->get('ViewHelperManager')->get('translate');
        $message->setSubject($translate($subject));

        return $this->mailService->send($message);        
    }
}
