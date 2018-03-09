<?php

namespace Drupal\pwa\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Default controller for the pwa module.
 */
class PWAController extends ControllerBase {

    public function pwa_serviceworker_file_data() {
        $query_string = \Drupal::state()->get('system.css_js_query_string') ?: 0;
        $path = drupal_get_path('module', 'pwa');
        $data = 'importScripts("/' . $path . '/js/serviceworker.js?' . $query_string . '");';

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

    public function pwa_serviceworker_notification() {
        $path = drupal_get_path('module', 'pwa');
        $query_string = \Drupal::state()->get('system.css_js_query_string') ?: 0;
        $data = 'importScripts("/' . $path . '/js/serviceworker-notification.js?' . $query_string . '");';

        return new Response($data, 200, [
            'Content-Type' => 'application/javascript',
            'Service-Worker-Allowed' => '/',
        ]);
    }

    public function pwa_got_subscription(Request $request) {
        $config = \Drupal::service('config.factory')->getEditable('pwa.config');
        $post = $request->getContent();
        $obj = json_decode($post, true);
        if (isset($obj['endpoint']) && isset($obj['keys']) && isset($obj['keys']["auth"])) {
            $noifications_subscriptions = $config->get('notifications_subscriptions');
            $notifications_subscriptions[$obj['keys']['p256dh']] = $obj;

            $config->set('notifications_subscriptions', $notifications_subscriptions)->save();

            return new Response('{ "data": { "success": true } }', 200, [
                'Content-Type' => 'application/json',
            ]);
        } else {
            return new Response('{"data": {"success": false}}', 200, [
                'Content-Type' => 'application/json',
            ]);

        }
    }

}
