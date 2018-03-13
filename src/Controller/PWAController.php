<?php

namespace Drupal\pwa\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\pwa\manifestClass;

/**
 * Default controller for the pwa module.
 */
class PWAController extends ControllerBase {

    public function pwa_serviceworker_file_data() {
        $query_string = \Drupal::state()->get('system.css_js_query_string') ?: 0;
        $path = drupal_get_path('module', 'pwa');
        $data = '
        importScripts("/' . $path . '/js/serviceworker.js?' . $query_string . '");';

        return new Response($data, 200, [
            'Content-Type' => 'application/javascript',
            'Service-Worker-Allowed' => '/',
        ]);
    }

    public function pwa_offline_page() {
        return [
            '#type' => 'html_tag',
            '#tag' => 'h1',
            '#value' => 'You are offline.',
            '#attributes' => [
                'data-drupal-pwa-offline' => true,
            ],
        ];
    }

    public function pwa_get_manifest() {
        $manifestClass = new manifestClass();
        $content = $manifestClass->get_output();


        return new Response($content, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    public function pwa_get_firebase_sw() {
        $path = drupal_get_path('module', 'pwa');
        $data = '
        importScripts("https://www.gstatic.com/firebasejs/4.8.1/firebase-app.js");
        importScripts("https://www.gstatic.com/firebasejs/4.8.1/firebase-messaging.js");
        importScripts("/' . $path . '/js/firebase-messaging-sw.js");';

        return new Response($data, 200, [
            'Content-Type' => 'application/javascript',
            'Service-Worker-Allowed' => '/',
        ]);
    }

    public function pwa_token_recieved(Request $request) {
        $config = \Drupal::service('config.factory')->getEditable('pwa.config');
        $post = $request->getContent();
        $obj = json_decode($post, true);
        $token = $obj['token'];

        //get tokens array
        $tokens = $config->get('tokens');

        if(!in_array($token, $tokens)) $tokens[] = $token;

        //save tokens
        $config->set('tokens', $tokens)->save();

        return new Response("", 200, [
            'Content-Type' => 'application/javascript',
        ]);
    }

}
