<?php

namespace Drupal\pwa\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\pwa\manifestClass;

class ConfigurationForm extends ConfigFormBase {

    /**
     * Returns a unique string identifying the form.
     *
     * @return string
     *   The unique string identifying the form.
     */
    public function getFormId() {
        return 'pwa_configuration_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('pwa.config');

        $form['name'] = array(
            "#type" => 'textfield',
            '#title' => $this->t('Web app name'),
            '#description' => $this->t("The name for the application that needs to be displayed to the user."),
            '#default_value' => $config->get('site_name'),
            '#required' => TRUE,
            "#maxlength" => 55,
            '#size' => 60,
        );

        $form['short_name'] = array(
            "#type" => 'textfield',
            "#title" => $this->t('Short name'),
            "#description" => $this->t("A short application name, this one gets displayed on the user's homescreen."),
            '#default_value' => $config->get('short_name'),
            '#required' => TRUE,
            '#maxlength' => 25,
            '#size' => 30,
        );

        $form['description'] = array(
            "#type" => 'textfield',
            "#title" => $this->t('Description'),
            "#description" => $this->t('The description of your pwa'),
            '#default_value' => $config->get('description'),
            '#maxlength' => 255,
            '#size' => 60,
        );

        $form['theme_color'] = array(
            "#type" => 'color',
            "#title" => $this->t('Theme color'),
            "#description" => $this->t('This color sometimes affects how the application is displayed by the OS.'),
            '#default_value' => $config->get('theme_color'),
            '#required' => TRUE,
        );

        $form['background_color'] = array(
            "#type" => 'color',
            "#title" => $this->t('Background color'),
            "#description" => $this->t('This color gets shown as the background when the application is launched'),
            '#default_value' => $config->get('background_color'),
            '#required' => TRUE,
        );

        $id = $this->getDisplayValue($config->get('display'), true);


        $form['display'] = array(
            "#type" => 'select',
            "#title" => $this->t('Display type'),
            "#description" => $this->t('This determines which UI elements from the OS are displayed.'),
            "#options" => [
                '1' => $this->t('fullscreen'),
                '2' => $this->t('standalone'),
                '3' => $this->t('minimal-ui'),
                '4' => $this->t('browser'),
            ],
            '#default_value' => $id,
            '#required' => TRUE,
        );

        $validators = array(
            'file_validate_extensions' => array('png'),
            'file_validate_image_resolution' => array('512x512', '512x512'),
        );


        $form['default_image'] = array(
            '#type' => 'checkbox',
            '#title' => 'Use the theme image',
            "#description" => 'This depends on the logo that the theme generates',
            "#default_value" => $config->get('default_image'),
        );

        //disable button when a custom image is added
        if ($config->get('default_image') == true) {
            $form['default_image']['#states']['checked'][':input[name="image[fids]"]']['value'] = '';
        }

        //the #states doesn't work ==> isue in Drupal core https://www.drupal.org/node/2847425
        //workaround: checkbox get's uncheck when uploading a custom image
        $form['image'] = array(
            '#type' => 'managed_file',
            '#name' => 'image',
            '#title' => t('Image'),
            '#size' => 20,
            '#description' => t('This image is your application icon. (png files only, format: (512x512)'),
            '#upload_validators' => $validators,
            '#upload_location' => 'public://pwa/',
        );

        //drupal issue https://www.drupal.org/project/drupal/issues/783438
        $bobTheHTMLBuilder = '<label>Current Image:</label> <br/> <img src="' . $config->get('image') . '" width="200"/>';
        if ($config->get('default_image') == 0)
            $form['current_image'] = array(
                '#markup' => $bobTheHTMLBuilder,
                '#name' => 'current image',
                '#id' => 'current_image',
            );

        return parent::buildForm($form, $form_state);
    }

    /**
     *
     * function converts an id to a display string or a string to an id
     *
     * @param $value
     * @param boolean $needId
     * @return int|string
     */
    private function getDisplayValue($value, $needId) {
        if ($needId) {
            $id = 1;
            switch ($value) {
                case 'standalone':
                    $id = 2;
                    break;
                case 'minimal-ui':
                    $id = 3;
                    break;
                case 'browser':
                    $id = 4;
                    break;
            }
            return $id;
        } else {
            $display = '';
            switch ($value) {
                case 1:
                    $display = 'fullscreen';
                    break;
                case 2:
                    $display = 'standalone';
                    break;
                case 3:
                    $display = 'minimal-ui';
                    break;
                case 4:
                    $display = 'browser';
                    break;
            }
        }
        return $display;
    }

    /**
     * function  checks if there is an image when switching from default to custom image
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        parent::validateForm($form, $form_state);

        $default_image = $form_state->getValue('default_image');
        $img = $form_state->getValue(['image', 0]);
        $config = \Drupal::config('pwa.config');

        if ($config->get('default_image') && !$default_image && !isset($img))
            $form_state->setErrorByName('image', $this->t('Upload a image, or chose the theme image.'));
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $manifestClass = new manifestClass();
        $config = $this->config('pwa.config');

        $display = $this->getDisplayValue($form_state->getValue('display'), false);

        $fid = $form_state->getValue(['image', 0]);
        $default_image = $form_state->getValue('default_image');

        if ($config->get('default_image') == 0) {
            if (isset($fid) || $default_image == 1) {
                $manifestClass->delete_image();
            }
        }

        $config->set('site_name', $form_state->getValue('name'))->save();
        $config->set('short_name', $form_state->getValue('short_name'))->save();
        $config->set('theme_color', $form_state->getValue('theme_color'))->save();
        $config->set('background_color', $form_state->getValue('background_color'))->save();
        $config->set('description', $form_state->getValue('description'))->save();
        $config->set('display', $display)->save();
        $config->set('default_image', $default_image)->save();

        if (!empty($fid)) {

            $file = File::load($fid);

            $file_usage = \Drupal::service('file.usage');
            $file->setPermanent();
            $file->save();

            $file_usage->add($file, 'PWA', 'PWA', \Drupal::currentUser()->id());

            //save new image
            $files_path = file_create_url("public://pwa") . '/';

            if (substr($files_path, 0, 7) == 'http://')
                $files_path = str_replace('http://', '', $files_path);
            elseif (substr($files_path, 0, 8) == 'https://')
                $files_path = str_replace('https://', '', $files_path);
            if (substr($files_path, 0, 4) == 'www.')
                $files_path = str_replace('www.', '', $files_path);
            $host = $_SERVER['HTTP_HOST'];
            if (substr($files_path, 0, strlen($host)) == $host)
                $files_path = str_replace($_SERVER['HTTP_HOST'], '', $files_path);


            $file_uri = $files_path . $file->getFilename();
            $file_path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://") . '/pwa/' . $file->getFilename();

            $config->set('image', $file_uri)->save();

            $newSize = 192;
            $oldSize = 512;

            $src = imagecreatefrompng($file_path);
            $dst = imagecreatetruecolor($newSize, $newSize);

            //make transparent background
            $color = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $color);
            imagesavealpha($dst, true);

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newSize, $newSize, $oldSize, $oldSize);
            $path_to_copy = \Drupal::service('file_system')->realpath(file_default_scheme() . "://") . '/pwa/' . $file->getFilename() . 'copy.png';
            if ($stream = fopen($path_to_copy, 'w+')) {
                imagepng($dst, $stream);
                $config->set('image_small', $files_path . $file->getFilename() . 'copy.png')->save();
            }
        }

        parent::submitForm($form, $form_state);
    }



    /**
     * Gets the configuration names that will be editable.
     *
     * @return array
     *   An array of configuration object names that are editable if called in
     *   conjunction with the trait's config() method.
     */
    protected function getEditableConfigNames() {
        return ['pwa.config'];
    }
}