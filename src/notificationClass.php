<?php

namespace Drupal\pwa;


class notificationClass {
    public function __construct() { }


    public function sendMessageToAllUsers($title, $message) {
        //todo: change to readonly
        $config = \Drupal::service('config.factory')->getEditable('pwa.config');
        $key = 'AAAALWTTurI:APA91bGhKU278wTlK45PGJ_cy4Ddh0dmc_oxlV47JSqgV30MmR4qfxITinadMuIoTlTTHjYLO74xyyilVANYzWUiFlt_GKqovcUgTiYOxA8InvP3ZIXSiQ9B0AbDoZFoJgov9m3vQYDR';
        $tokens = $config->get('tokens');
        $des = '';
        foreach ($tokens as $token) {
            $des .= 'message send to: ' . $token . '; ';
            $url = 'https://fcm.googleapis.com/fcm/send';
            $response = \Drupal::httpClient()->post($url, [
                'json' => [
                    "to" => $token,
                    "notification" => [
                        "body" => $message,
                        "title" => $title,
                    ],
                ],
                'headers' => [
                    'Content-type' => 'application/json',
                    'Authorization' => 'key=' . $key,
                ],
            ])->getBody()->getContents();
        }


        $config->set('description', $des)->save();

    }
}