<?php
namespace Drupal\pwa\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pwa\notificationClass;


class  NotificationForm extends ConfigFormBase {

    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = \Drupal::config('pwa.config');


        $form['apiKey'] = [
            "#type" => 'textfield',
            "#title"=> $this->t("apiKey"),
            "#required" => true,
            "#default_value" => $config->get('apiKey'),
            "#maxlength" => 160,
        ];
        $form['authDomain'] = [
            "#type" => 'textfield',
            "#title"=> $this->t("authDomain"),
            "#required" => true,
            "#default_value" => $config->get('authDomain'),
            "#maxlength" => 160,
        ];
        $form['databaseURL'] = [
            "#type" => 'textfield',
            "#title"=> $this->t("databeseURL"),
            "#required" => true,
            "#default_value" => $config->get('databaseURL'),
            "#maxlength" => 160,
        ];
        $form['projectId'] = [
            "#type" => 'textfield',
            "#title"=> $this->t("projectId"),
            "#required" => true,
            "#default_value" => $config->get('projectId'),
            "#maxlength" => 160,
        ];
        $form['storageBucket'] = [
            "#type" => 'textfield',
            "#title"=> $this->t("storageBucket"),
            "#required" => true,
            "#default_value" => $config->get('storageBucket'),
            "#maxlength" => 160,
        ];
        $form['messagingSenderId'] = [
            "#type" => 'textfield',
            "#title"=> $this->t("messagingSenderId"),
            "#required" => true,
            "#default_value" => $config->get('messagingSenderId'),
            "#maxlength" => 160,
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

        $config->set('apiKey', $form_state->getValue('apiKey'))->save();
        $config->set('authDomain', $form_state->getValue('authDomain'))->save();
        $config->set('databaseURL', $form_state->getValue('databaseURL'))->save();
        $config->set('projectId', $form_state->getValue('projectId'))->save();
        $config->set('storageBucket', $form_state->getValue('storageBucket'))->save();
        $config->set('messagingSenderId', $form_state->getValue('messagingSenderId'))->save();
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