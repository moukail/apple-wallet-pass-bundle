<?php

namespace Moukail\AppleWalletPassBundle\MessageHandler;

use Moukail\AppleWalletPassBundle\Message\ApplePassPushNotification;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ApplePassPushNotificationHandler
{
    private LoggerInterface $logger;
    private ParameterBagInterface $params;

    /**
     * ApplePassPushNotificationHandler constructor.
     *
     * @param LoggerInterface $logger
     * @param ParameterBagInterface $params
     */
    public function __construct(LoggerInterface $logger, ParameterBagInterface $params)
    {
        $this->logger = $logger;
        $this->params = $params;
    }

    public function __invoke(ApplePassPushNotification $message)
    {
        $appleConfig = $this->params->get('apple');
        $projectRoot = $this->params->get('kernel.project_dir');

        $apnsHost = 'gateway.push.apple.com';
        $apnsPort = 2195;
        $pushToken = $message->getPushToken();
        $passIdentify = $appleConfig['pass_type_identifier'];

        // The content that is returned by the LiveCode “pushNotificationReceived” message.
        $payload = ['aps' => []];
        $payload = json_encode($payload);

        /**
         * The OpenSSL command below will generate a 2048-bit RSA private key and CSR:
         * openssl req -newkey rsa:2048 -keyout PRIVATEKEY.key -out MYCSR.csr
         * generate local cert file from p12 file with this command
         * openssl pkcs12 -in swkgroep.p12 -nodes -out swkgroep.pem
         */
        $contextOptions = [
            'ssl' => [
                'cafile' => $projectRoot . $appleConfig['apns_ca_file'],
                'local_cert' => $projectRoot . $appleConfig['apns_cert_file'],
                'passphrase' => $appleConfig['p12_pass'],
                'disable_compression' => true,
            ],
        ];

        // Create the Socket Stream.
        $streamContext = stream_context_create($contextOptions);

        // Open the Connection to the APNS Server.
        //$apns = stream_socket_client('ssl://'.$apnsHost.':'.$apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $streamContext);

        $apns = stream_socket_client('ssl://'.$apnsHost.':'.$apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
        stream_set_blocking($apns, 0);

        // Check if we were able to open a socket.
        if (!$apns) {
            $this->logger->error('ApplePassPushNotificationHandler:error', [
                'error' => $error,
                'errorString' => $errorString,
                'message' => 'APNS Connection Failed',
            ]);

            return;
        }

        // Build the Binary Notification.
        //$tMsg = chr (0) . chr (0) . chr (32) . pack ('H*', $pushToken) . pack ('n', strlen ($payload)) . $payload;
        $tMsg = chr(0).pack('n', 32).pack('H*', $pushToken).pack('n', strlen($payload)).$payload.pack('n', strlen($passIdentify)).$passIdentify;

        // Send the Notification to the Server.
        $tResult = fwrite($apns, $tMsg, strlen($tMsg));

        if (!$tResult) {
            // todo delete device
            $this->logger->warning('ApplePassPushNotificationHandler:warning', [
                'message' => 'Could not Deliver Message to APNS',
            ]);

            return;
        }

        $this->logger->alert('ApplePassPushNotificationHandler:alert', [
            'message' => 'Delivered Message to APNS',
        ]);

        // Close the Connection to the Server.
        fclose($apns);
    }
}
