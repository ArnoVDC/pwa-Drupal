<?php

namespace Drupal\pwa\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pwa\notificationClass;


class  NotificationForm extends ConfigFormBase {

    private $firebase_code, $firebase_code_clean;

    /**
     *
     * @param array $form
     * @param FormStateInterface $form_state
     * @return array
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = \Drupal::config('pwa.config');

        $form['firebase_code'] = [
            "#type" => 'textarea',
            "#title" => $this->t("Firebase configuration code"),
            "#required" => true,
            "#default_value" => $config->get('firebase_code'),
            '#description' => $this->t('Copy and past your firebase configurationcode (for web) here. You can copy the code with the script tags. Learn how right <a target="_blank" href="https://firebase.google.com/docs/web/setup">here</a>.')
        ];
        $form['keyPair'] = [
            "#type" => 'textfield',
            "#title" => $this->t("Webpush certificate, public key"),
            "#description" => $this->t('You can find/generate tis key under firebase settings->cloud messaging'),
            "#required" => true,
            "#default_value" => $config->get('keyPair'),
            "#maxlength" => 160,        ];

        $form['key'] = [
            "#type" => 'textfield',
            "#title" => $this->t("Server key"),
            "#description" => $this->t('You can find the server key under firebase settings->cloud messaging. Make sure you don\'t use the old one.'),
            "#required" => true,
            "#default_value" => $config->get('server_key'),
            "#maxlength" => 160,
        ];

        $form['title'] = [
            "#type" => 'textfield',
            '#title' => $this->t('Title'),
            '#required' => TRUE,
            "#maxlength" => 100,
        ];

        $form['message'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Message'),
            '#required' => TRUE,
            "#maxlength" => 255,
        ];

        $out = parent::buildForm($form, $form_state);
        $out['actions']['submit']['#value'] = 'Send notification';

        return $out;
    }

    /**
     * function checks if the firebase code is right
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        parent::validateForm($form, $form_state);

        $this->firebase_code = $form_state->getValue('firebase_code');
        $this->firebase_code_clean = $this->getCleanFirebaseCode($this->firebase_code);

        $this->firebase_code_clean = json_decode($this->firebase_code_clean, true);

        if ($this->firebase_code_clean['apiKey'] == '' ||
            $this->firebase_code_clean['authDomain'] == '' ||
            $this->firebase_code_clean['databaseURL'] == '' ||
            $this->firebase_code_clean['projectId'] == '' ||
            $this->firebase_code_clean['storageBucket'] == '' ||
            $this->firebase_code_clean['messagingSenderId'] == ''
        ) {
            $s = htmlentities('Could not handle firebase code, make sure all of the code is copied. (ex.:"<script src= ... firebase.initializeApp(config);</script>")');
            $form_state->setErrorByName('firebase_code', $this->t($s));
        }

    }

    /**
     * function makes the firebase code a json string
     * @param $firebase_code
     * @return mixed|null|string|string[]
     */
    private function getCleanFirebaseCode($firebase_code) {
        $firebase_code = preg_replace('<.*?script.*\/?>', '', $firebase_code);
        $firebase_code = str_replace('// Initialize Firebase', '', $firebase_code);
        $firebase_code = str_replace('var config = ', '', $firebase_code);
        $firebase_code = str_replace('firebase.initializeApp(config);', '', $firebase_code);
        $firebase_code = str_replace(';', '', $firebase_code);
        $firebase_code = preg_replace('(\r|\n)', '', $firebase_code);
        $firebase_code = trim($firebase_code);

        //put names between ""
        $firebase_code = str_replace('apiKey', '"apiKey"', $firebase_code);
        $firebase_code = str_replace('authDomain', '"authDomain"', $firebase_code);
        $firebase_code = str_replace('databaseURL', '"databaseURL"', $firebase_code);
        $firebase_code = str_replace('projectId', '"projectId"', $firebase_code);
        $firebase_code = str_replace('storageBucket', '"storageBucket"', $firebase_code);
        $firebase_code = str_replace('messagingSenderId', '"messagingSenderId"', $firebase_code);

        //start and end with {}
        if (substr($firebase_code, 0, 1) != '{')
            $firebase_code = '{' . $firebase_code;
        if (substr($firebase_code, strlen($firebase_code) - 1, 1) != '}')
            $firebase_code .= '}';

        return $firebase_code;
    }

    /**
     * function saves all values and sends the message
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = \Drupal::service('config.factory')->getEditable('pwa.config');

        $config->set('firebase_code', $this->firebase_code)->save();
        $config->set('firebase_code_clean', $this->firebase_code_clean)->save();

        $config->set('apiKey', $this->firebase_code_clean['apiKey'])->save();
        $config->set('authDomain', $this->firebase_code_clean['authDomain'])->save();
        $config->set('databaseURL', $this->firebase_code_clean['databaseURL'])->save();
        $config->set('projectId', $this->firebase_code_clean['projectId'])->save();
        $config->set('storageBucket', $this->firebase_code_clean['storageBucket'])->save();
        $config->set('messagingSenderId', $this->firebase_code_clean['messagingSenderId'])->save();

        //codes still to get
        $config->set('keyPair', $form_state->getValue('keyPair'))->save();
        $config->set('server_key', $form_state->getValue('key'))->save();

        //send notification
        $notificationClass = new notificationClass();

        $notificationClass->sendMessageToAllUsers(
            $form_state->getValue('title'),
            $form_state->getValue('message')
        );

        parent::submitForm($form, $form_state);
    }

    /**
     * Returns a unique string identifying the form.
     *
     * @return string
     *   The unique string identifying the form.
     */
    public function getFormId() {
        return 'pwa_notification_form';
    }

    /**
     * Gets the configuration names that will be editable.
     *
     * @return array
     *   An array of configuration object names that are editable if called in
     *   conjunction with the trait's config() method.
     */
    protected function getEditableConfigNames() {
        return ['pwa.notifications.config'];
    }
}