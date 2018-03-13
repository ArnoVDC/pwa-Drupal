<?php
namespace Drupal\pwa\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pwa\notificationClass;


class  NotificationForm extends ConfigFormBase {

    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['title'] = [
            "#type" => 'textfield',
            '#title' => $this->t('Title'),
            '#required' => TRUE,
            "#maxlength" => 100,
            '#size' => 100,
        ];

        $form['message'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Message'),
            '#required' => TRUE,
            "#maxlength" => 255,
            '#size' => 255,
        ];

        return parent::buildForm($form, $form_state);
    }


    public function submitForm(array &$form, FormStateInterface $form_state) {
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