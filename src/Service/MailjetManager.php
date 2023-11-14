<?php

namespace App\Service;

use Mailjet\Client;
use Mailjet\Resources;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class MailjetManager
{
    private $params;

    public function __construct(ContainerBagInterface $params)
    {
        $this->params = $params;
    }

    public function send($email, $name, $subject, $content)
    {
        $mj = new Client($this->params->get('app.mailjet_public'), $this->params->get('app.mailjet_endpoint'),true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $this->params->get('app.mailjet_email'),
                        'Name' => "AD"
                    ],
                    'To' => [
                        [
                            'Email' => $email,
                            'Name' => $name
                        ]
                    ],
                    'TemplateID' => 4027862,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'name' => $name,
                        'content' => $content
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
//        $response->success() && var_dump($response->getData());
        return $response->success();
    }
}
