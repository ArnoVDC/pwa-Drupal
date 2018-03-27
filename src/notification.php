<?php
/**
 * Created by PhpStorm.
 * User: arnov
 * Date: 26/03/2018
 * Time: 14:14
 */

namespace Drupal\pwa;


class notification {

  public function __construct() { }

  /**
   * function to send a notification to all the users
   *
   * @param $title
   * @param $message
   */
  public function sendMessageToAllUsers($title, $message) {
    $config = \Drupal::service('config.factory')->getEditable('pwa.config');
    $tokens = $config->get('tokens');
    $key = $config->get('server_key');
    //note: this is the image that is used for the manifest file
    //see isue: https://www.drupal.org/project/pwa/issues/2954461 for this patch
    $image = $config->get('image');
    $image = 'https://' . $_SERVER['HTTP_HOST'] . $image;

    foreach ($tokens as $token) {
      $url = 'https://fcm.googleapis.com/fcm/send';
      $response = \Drupal::httpClient()->post($url, [
        'json' => [
          "to" => $token,
          "notification" => [
            "body" => $message,
            "title" => $title,
            "click_action" => "https://" . $_SERVER['HTTP_HOST'],
            'icon' => $image,
          ],
        ],
        'headers' => [
          'Content-type' => 'application/json',
          'Authorization' => 'key=' . $key,
        ],
      ])->getBody()->getContents();

      $a = json_decode($response, TRUE);
      if ($a["failure"] == 1) {
        $id = array_search($token, $tokens);
        unset($id, $tokens);
      }
    }
    $config->set("tokens", $tokens)->save();
  }
}
