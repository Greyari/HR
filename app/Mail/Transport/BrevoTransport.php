<?php
namespace App\Mail\Transport;

use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportException;
use GuzzleHttp\Client;

class BrevoTransport extends AbstractTransport
{
    protected Client $client;
    protected string $apiKey;

    public function __construct(Client $client, string $apiKey)
    {
        parent::__construct(); // wajib panggil parent constructor
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    protected function doSend(SentMessage $sentMessage): void
    {
        $message = $sentMessage->getOriginalMessage();

        if (!$message instanceof Email) {
            throw new \LogicException('Message must be an instance of Email.');
        }

        $to = [];
        foreach ($message->getTo() as $address) {
            $to[] = ['email' => $address->getAddress()];
        }

        $htmlContent = $message->getHtmlBody() ?? $message->getTextBody();

        try {
            $this->client->post('https://api.brevo.com/v3/smtp/email', [
                'headers' => [
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'sender' => [
                        'name' => $message->getFrom()[0]->getName(),
                        'email' => $message->getFrom()[0]->getAddress()
                    ],
                    'to' => $to,
                    'subject' => $message->getSubject(),
                    'htmlContent' => $htmlContent,
                ],
            ]);
        } catch (\Exception $e) {
            throw new TransportException('BrevoTransport failed: ' . $e->getMessage());
        }
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
