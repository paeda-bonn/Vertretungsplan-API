<?php
require __DIR__ . '/../vendor/autoload.php';

use Pushok\AuthProvider;
use Pushok\Client;
use Pushok\Notification;
use Pushok\Payload;
use Pushok\Payload\Alert;

class PushAPN
{
    private $deviceids = ["853e7cc42c2e80640118a5f2e76eb5e2bfa0c02a37d86f348b945923af71749f"];
    private $isProduction = true;
    private $keyId = "3DX2L7BZCG";
    private $teamId = "P29L6CUAA4";
    private $apns_topic = 'de.nils-witt.oks.ios.vertretungsplan';
    private $p8file = __DIR__ . "/../config/keys/apn/AuthKey_3DX2L7BZCG.p8";


    function push($message){
        $options = [
            'key_id' => $this->keyId,
            'team_id' => $this->teamId,
            'app_bundle_id' => $this->apns_topic,
            'private_key_path' => $this->p8file,
            'private_key_secret' => null
        ];

        $authProvider = AuthProvider\Token::create($options);
        $alert = Alert::create()->setTitle('Updated');
        $alert = $alert->setBody($message);
        $payload = Payload::create()->setAlert($alert);
        $payload->setSound('default');

        $notifications = [];
        foreach ($this->deviceids as $deviceToken) {
            $notifications[] = new Notification($payload, $deviceToken);
        }

        $client = new Client($authProvider, $production = $this->isProduction);
        $client->addNotifications($notifications);

        $responses = $client->push();

        foreach ($responses as $response) {
            $response->getApnsId();
            $response->getStatusCode();
            $response->getReasonPhrase();
            $response->getErrorReason();
            $response->getErrorDescription();
        }
    }
}