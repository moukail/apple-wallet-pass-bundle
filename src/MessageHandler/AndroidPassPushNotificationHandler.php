<?php

namespace Moukail\AppleWalletPassBundle\MessageHandler;

use Moukail\AppleWalletPassBundle\Message\AndroidPassPushNotification;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AndroidPassPushNotificationHandler
{
    private LoggerInterface $logger;
    private ParameterBagInterface $params;

    /**
     * AndroidPassPushNotificationHandler constructor.
     *
     * @param LoggerInterface $logger
     * @param ParameterBagInterface $params
     */
    public function __construct(LoggerInterface $logger, ParameterBagInterface $params)
    {
        $this->logger = $logger;
        $this->params = $params;
    }

    public function __invoke(AndroidPassPushNotification $message)
    {
        $appleConfig = $this->params->get('apple');
        $walletpassesConfig = $this->params->get('walletpasses');
        $client = new \GuzzleHttp\Client();

        $base_url = 'https://walletpasses.appspot.com/api/v1/push'; //getenv('TICKET_SCAN_URL');

        $response = $client->request('POST', $base_url, [
            'headers' => [
                'Authorization' => $walletpassesConfig['token'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'verify' => false,
            'force_ip_resolve' => 'v4',
            'json' => [
                'passTypeIdentifier' => $appleConfig['pass_type_identifier'],
                'pushTokens' => $message->getPushToken(),
            ],
        ]);

        if (200 != $response->getStatusCode()) {
            $this->logger->warning('AndroidPassPushNotificationHandler:warning', [
                'ReasonPhrase' => $response->getReasonPhrase(),
                'StatusCode' => $response->getStatusCode(),
            ]);

            return;
        }

        $result = json_decode($response->getBody());

        $this->logger->alert('AndroidPassPushNotificationHandler:alert', [
            'message' => 'Delivered Message to walletpasses',
            'result' => $result,
        ]);
    }
}
