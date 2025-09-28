<?php

namespace App\Helpers;

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;

class BrevoMailer
{
    private $apiInstance;

    public function __construct()
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', env('BREVO_API_KEY'));
        $this->apiInstance = new TransactionalEmailsApi(new Client(), $config);
    }

    public function send($toEmail, $toName, $subject, $htmlContent)
    {
        $sendSmtpEmail = new SendSmtpEmail([
            'subject' => $subject,
            'sender' => ['name' => env('MAIL_FROM_NAME'), 'email' => env('MAIL_FROM_ADDRESS')],
            'to' => [[ 'email' => $toEmail, 'name' => $toName ]],
            'htmlContent' => $htmlContent,
        ]);

        return $this->apiInstance->sendTransacEmail($sendSmtpEmail);
    }
}
