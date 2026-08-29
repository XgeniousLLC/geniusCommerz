<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use Aws\Sns\SnsClient;

/**
 * Amazon SNS.
 *
 * Uses the AWS SDK rather than raw HTTP because SNS requires SigV4 request signing, which
 * is not something to hand-roll. The SDK is already present as a dependency of
 * league/flysystem-aws-s3-v3.
 */
class AwsSnsGateway extends ProviderDriver implements SmsInterface
{
    public function send(string $to, string $message): bool
    {
        $attributes = [
            'AWS.SNS.SMS.SMSType' => ['DataType' => 'String', 'StringValue' => 'Transactional'],
        ];

        if ($senderId = $this->cred('sender_id')) {
            $attributes['AWS.SNS.SMS.SenderID'] = ['DataType' => 'String', 'StringValue' => $senderId];
        }

        $result = $this->client()->publish([
            'Message'           => $message,
            'PhoneNumber'       => $to,           // SNS requires E.164
            'MessageAttributes' => $attributes,
        ]);

        return ! empty($result['MessageId']);
    }

    /** SNS reports spend through Cost Explorer, not an account balance. */
    public function balance(): ?string
    {
        return null;
    }

    public function name(): string
    {
        return 'Amazon SNS';
    }

    private function client(): SnsClient
    {
        return new SnsClient([
            'version'     => 'latest',
            'region'      => $this->cred('region', 'us-east-1'),
            'credentials' => [
                'key'    => (string) $this->cred('access_key_id'),
                'secret' => (string) $this->cred('secret_access_key'),
            ],
        ]);
    }
}
