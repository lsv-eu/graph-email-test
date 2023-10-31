<?php

namespace App\Mail;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class MsGraphTransport extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        /** @var \Symfony\Component\Mime\Email $email */
        $email = $message->getOriginalMessage();

        $graphMessage = [
            'subject' => $email->getSubject(),
            'body' => [
                'contentType' => 'html',
                'content' => $email->getHtmlBody(),
            ],
            'toRecipients' => array_map(fn ($recipient) => [
                'emailAddress' => [
                    'address' => $recipient->getAddress(),
                    'name' => $recipient->getName(),
                ],
            ], $email->getTo()),
            'bccRecipients' => array_map(fn ($recipient) => [
                'emailAddress' => [
                    'address' => $recipient->getAddress(),
                    'name' => $recipient->getName(),
                ],
            ], $email->getBcc()),
            'ccRecipients' => array_map(fn ($recipient) => [
                'emailAddress' => [
                    'address' => $recipient->getAddress(),
                    'name' => $recipient->getName(),
                ],
            ], $email->getCc()),
            'attachments' => array_map(fn ($attachment) => [
                '@odata.type' => '#microsoft.graph.fileAttachment',
                'name' => $attachment->getFilename(),
                'contentType' => $attachment->getContentType(),
                'contentBytes' => base64_encode($attachment->getBody()),
            ], $email->getAttachments()),
        ];

        $response = app('microsoftgraph')
            ->createRequest('POST', "/users/{$email->getFrom()[0]->getAddress()}/sendMail")
            ->attachBody(['message' => $graphMessage])
            ->execute();

        if (! $response->getStatus() == 202) {
            logger()->error('Could not send email to MS Graph: '.$response->getReasonPhrase());
            throw new \Exception('Could not send email to MS Graph: '.$response->getReason);
        }
    }

    public function __toString(): string
    {
        return 'microsoft-graph';
    }
}
