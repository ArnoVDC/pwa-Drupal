<?php

namespace Drupal\pwa;

class manifestClass {

  private $manifestUri = '';

  public function __construct() {
    $this->manifestUri = '/manifest.json';
  }

  /**
   *Function created the manifest json string based on the configurations.
   *
   * @return string
   *  manifest json string.
   */
  public function get_output() {
    //get values
    $values = $this->getCleanValues();


    //create output
    $output = '
        {
          "name": "' . $values['site_name'] . '",   
          "short_name": "' . $values['short_name'] . '",
          "start_url": ".",
          "display": "' . $values['display'] . '",
          "background_color": "' . $values['background_color'] . '",
          "theme_color": "' . $values['theme_color'] . '",
          "description": "' . $values['description'] . '",
          "lang": "' . $values['lang'] . '",
          "icons": [{
              "src": "' . $values['image_small'] . '",
              "sizes": "192x192",
              "type": "image/png"
            },
            {
                "src": "' . $values['image'] . '",
                "sizes": "512x512",
                "type":"image/png"
            }
          ],
          "scope": "/",
          "gcm_sender_id": "103953800507"
        }';


    return $output;
  }

  /**
   * function checks the values in config and add default value if necessary.
   *
   * @return array
   *  the values from the configuration.
   */
  private function getCleanValues() {
    $output = [];
    $input = [];

    //change config lang
    $language_manager = \Drupal::languageManager();
    $lang = $language_manager->getCurrentLanguage()->getId();
    $language = $language_manager->getLanguage($lang);
    $language_manager->setConfigOverrideLanguage($language);
    $config_get = \Drupal::config('pwa.config');

    $config = \Drupal::service('config.factory')->getEditable('pwa.config');

    $input['site_name'] = $config_get->get('site_name');
    $input['short_name'] = $config_get->get('short_name');
    $input['background_color'] = $config_get->get('background_color');
    $input['theme_color'] = $config_get->get('theme_color');
    $input['image'] = $config_get->get('image');
    $input['display'] = $config_get->get('display');
    $input['default_image'] = $config_get->get('default_image');
    $input['image_small'] = $config_get->get('image_small');

    if ($input['default_image'] == TRUE) {
      $input['image'] = theme_get_setting('logo')['url'];
      $input['image_small'] = $input['image'];
    }

    foreach ($input as $key => $value) {
      if ($value !== '') {
        $output[$key] = $value;
      }
      elseif ($config->get($key) !== '') {
        $output[$key] = $config->get($key);
      }
      else {
        if ($key === 'background_color' || $key === 'theme_color') {
          $output[$key] = '#ffffff';
          $config->set($key, '#ffffff')->save();
        }
        else {
          if ($key === 'image' && $input['dafault_image'] != 1) {
            $output[$key] = 'url/to/default/img';
            $config->set($key, 'url/to/default/img')->save();
          }
          else {
            if ($key == 'display') {
              $output[$key] = 'standalone';
              $config->set($key, 'standalone')->save();
            }
            else {
              $output[$key] = 'default value for ' . $key . ', go to configuration to change';
              $config->set($key, 'default value for ' . $key . ', go to configuration to change')
                ->save();
            }
          }
        }
      }
    }
    //values that's not required
    $output['description'] = $config_get->get('description');

    return $output;
  }

  /**
   *function deletes the images that are used for the manifest file.
   */
  public function delete_image() {
    $config = \Drupal::config('pwa.config');
    $path = getcwd() . $config->get('image');
    unlink($path);
    $path .= 'copy.png';
    unlink($path);
  }
}
