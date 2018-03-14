<?php
namespace Drupal\pwa\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pwa\notificationClass;


class  NotificationForm extends ConfigFormBase {

    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = \Drupal::config('pwa.config');

        $form['firebase_code'] = [
            "#type" => 'textarea',
            "#title"=> $this->t("Firebase configuration code"),
            "#required" => true,
            "#default_value" => $config->get('firebase_code'),
            '#description'=> $this->t('Copy and past your firebase configurationcode (for web) here. You can copy the code with the script tags.')
        ];
        $form['keyPair'] = [
            "#type" => 'textfield',
            "#title"=> $this->t("Webpush certificate, public key"),
            "#required" => true,
            "#default_value" => $config->get('keyPair'),
            "#maxlength" => 160,
        ];

        $form['key'] = [
            "#type" => 'textfield',
            "#title"=> $this->t("server key"),
            "#description" => $this->t('You can find the server key in you firebase settings'),
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

        return parent::buildForm($form, $form_state);
    }


    public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = \Drupal::service('config.factory')->getEditable('pwa.config');

        $firebase_code = $form_state->getValue('firebase_code');
        $firebase_code_clean = $this->getCleanFirebaseCode($firebase_code);

        $config->set('firebase_code', $firebase_code)->save();
        $config->set('firebase_code_clean', $firebase_code_clean)->save();

        $firebase_code_clean = json_decode($firebase_code_clean,true);

        $config->set('description', $firebase_code_clean['apiKey'])->save();

        $config->set('apiKey', $firebase_code_clean['apiKey'])->save();
        $config->set('authDomain',  $firebase_code_clean['authDomain'])->save();
        $config->set('databaseURL',  $firebase_code_clean['databaseURL'])->save();
        $config->set('projectId',  $firebase_code_clean['projectId'])->save();
        $config->set('storageBucket',  $firebase_code_clean['storageBucket'])->save();
        $config->set('messagingSenderId',  $firebase_code_clean['messagingSenderId'])->save();

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

    private function getCleanFirebaseCode($firebase_code){
        $firebase_code = preg_replace('<.*?script.*\/?>','',$firebase_code);
        $firebase_code = str_replace('// Initialize Firebase', '', $firebase_code);
        $firebase_code = str_replace('var config = ', '', $firebase_code);
        $firebase_code = str_replace('firebase.initializeApp(config);', '', $firebase_code);
        $firebase_code =str_replace(';', '', $firebase_code);
        $firebase_code = preg_replace('(\r|\n)', '', $firebase_code);
        $firebase_code = trim($firebase_code);

        //put names between ""
        $firebase_code = str_replace('apiKey', '"apiKey"', $firebase_code);
        $firebase_code = str_replace('authDomain', '"authDomain"', $firebase_code);
        $firebase_code = str_replace('databaseURL', '"databaseURL"', $firebase_code);
        $firebase_code = str_replace('projectId', '"projectId"', $firebase_code);
        $firebase_code = str_replace('storageBucket', '"storageBucket"', $firebase_code);
        $firebase_code = str_replace('messagingSenderId', '"messagingSenderId"', $firebase_code);

        return $firebase_code;
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

    /**
     * Returns a unique string identifying the form.
     *
     * @return string
     *   The unique string identifying the form.
     */
    public function getFormId() {
        return 'pwa_notification_form';
    }
}