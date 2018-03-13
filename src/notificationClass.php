<?php

namespace Drupal\pwa;


class notificationClass {
    public function __construct() { }

    public function sendMessageToAllUsers($title, $message) {
        $config = \Drupal::service('config.factory')->getEditable('pwa.config');
        $tokens = $config->get('tokens');
        $key = $config->get('server_key');
        foreach ($tokens as $token) {
            $url = 'https://fcm.googleapis.com/fcm/send';
            $response = \Drupal::httpClient()->post($url, [
                'json' => [
                    "to" => $token,
                    "notification" => [
                        "body" => $message,
                        "title" => $title,
                        "click_action"=> "https://" . $_SERVER['HTTP_HOST'],
                        "icon" => 'http://localhost.com/sites/default/files/pwa/metal_chain_fence_png_stock_cc1_large_by_annamae22-da7lguz.pngcopy.png',
                    ],
                ],
                'headers' => [
                    'Content-type' => 'application/json',
                    'Authorization' => 'key=' . $key,
                ],
            ])->getBody()->getContents();

            $a = json_decode($response, true);
            if ($a["failure"] == 1) {
                $id = array_search($token, $tokens);
                unset($id, $tokens);
            }
        }

        $config->set("tokens", $tokens)->save();
    }
}