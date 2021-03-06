<?php

namespace Drupal\pwa\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\pwa\manifest;

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
        'data-drupal-pwa-offline' => TRUE,
      ],
    ];
  }

  /**
   * route generates the manifest file for the browser
   *
   * @return Response
   */
  public function pwa_get_manifest() {
    $manifestClass = new manifest();
    $content = $manifestClass->get_output();

    return new Response($content, 200, [
      'Content-Type' => 'application/json',
    ]);
  }

  /**
   * returns a firebase service worker: a javascript that can handle the
   * notification
   *
   * @return Response
   */
  public function pwa_get_firebase_sw() {
    $config = \Drupal::config('pwa.config');
    $messagingKey = $config->get('messagingSenderId');

    $data = '
        importScripts("https://www.gstatic.com/firebasejs/4.8.1/firebase-app.js");
        importScripts("https://www.gstatic.com/firebasejs/4.8.1/firebase-messaging.js");
        firebase.initializeApp({\'messagingSenderId\': \'' . $messagingKey . '\'});
        const messaging = firebase.messaging();';

    return new Response($data, 200, [
      'Content-Type' => 'application/javascript',
      'Service-Worker-Allowed' => '/',
    ]);
  }

  /**
   * function receives the user token and save's it in the configurations
   *
   * @param Request $request
   *
   * @return Response
   */
  public function pwa_token_received(Request $request) {
    $config = \Drupal::service('config.factory')->getEditable('pwa.config');
    $post = $request->getContent();
    $obj = json_decode($post, TRUE);
    $token = $obj['token'];

    //get tokens array
    $tokens = $config->get('tokens');
    if (!in_array($token, $tokens)) {
      $tokens[] = $token;
    }

    //save tokens
    $config->set('tokens', $tokens)->save();

    return new Response('{"data": "success"}', 200, [
      'Content-Type' => 'application/json',
    ]);
  }

  /**
   * response with the configuration that the client javascript needs to
   * connect to firebase
   *
   * @return Response
   */
  public function pwa_firebase_config() {
    $config = \Drupal::config('pwa.config');

    $apiKey = $config->get('apiKey');
    $authDomain = $config->get('authDomain');
    $databaseURL = $config->get('databaseURL');
    $projectId = $config->get('projectId');
    $storageBucket = $config->get('storageBucket');
    $messagingSenderId = $config->get('messagingSenderId');
    $keyPair = $config->get('keyPair');

    $data = '{"config": { "apiKey": "' . $apiKey . '", "authDomain": "' .
      $authDomain . '", "databaseURL": "' . $databaseURL . '",
      "projectId": "' . $projectId . '",
      "storageBucket": "' . $storageBucket . '",
      "messagingSenderId": "' . $messagingSenderId . '" }, 
         "publicClientkey": "' . $keyPair . '"
      }';


    return new Response($data, 200, [
      'Content-Type' => 'application/json',
    ]);
  }

}
