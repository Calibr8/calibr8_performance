<?php

namespace Drupal\calibr8_performance\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Configure example settings for this site.
 */
class DeleteResourcehintForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'calibr8_performance_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'calibr8_performance.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('calibr8_performance.settings');
    $config_data = $config->getRawData();

    $route_match = \Drupal::service('current_route_match');
    $remove_id = (Int)$route_match->getParameter('rid');

    if(!isset($config_data['resource_hints'][$remove_id])) {
      drupal_set_message($this->t('Resource hint not found'), 'error');
      return null;
    } else {
      $resource_hint = $config_data['resource_hints'][$remove_id]['resource_hint'];
      $url = $config_data['resource_hints'][$remove_id]['url'];
      $form['confirmation_message'] = [
        '#type' => 'markup',
        '#markup' => $this->t('Remove resource hint %hint for %url ?', ['%hint' => $resource_hint, '%url' => $url]),
      ];
      $form['remove_id'] = [
        '#type' => 'hidden',
        '#value' => $remove_id,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('calibr8_performance.settings');
    $config_data = $config->getRawData();
    $remove_id = $form['remove_id']['#value'];

    if(isset($config_data['resource_hints'][$remove_id])) {
      unset($config_data['resource_hints'][$remove_id]);
      $config->set('resource_hints', $config_data['resource_hints'])->save();
    }

    // redirect to previous page
    $response = new TrustedRedirectResponse('/' . CALIBR8_PERFORMANCE_SETTINGS_PATH);
    $response->send();
  }

}