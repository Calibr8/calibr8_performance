<?php

namespace Drupal\calibr8_performance\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class SettingsForm extends ConfigFormBase {

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

    // Filter settings
    $resource_hints = $config->get('resource_hints');
    $resource_hints_values = [
      'dns-prefetch' => 'dns-prefetch',
      'preconnect' => 'preconnect',
      'prefetch' => 'prefetch',
      'prerender' => 'prerender'
    ];

    if(!$resource_hints) {
      $resource_hints = [];
    }

    $form['#attached'] = [
      'library' => [
        'calibr8_performance/settings_form',
      ],
    ];

    $form['resource_hints_table'] = array(
      '#type' => 'table',
      '#header' => array(t('Resource hint'), t('Url'), t('Actions'), t('Weight')),
      '#tableselect' => FALSE,
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-order-weight',
        ),
      ),
    );

    $rows = [];
    foreach ($resource_hints as $index => $resource_hint) {
      $rows[$index]['#attributes']['class'] = ['draggable', 'row-index-' . $index];
      $rows[$index]['resource_hint'] = array(
        '#type' => 'select',
        '#title' => $this->t('Resource hint'),
        '#title_display' => 'invisible',
        '#options' => $resource_hints_values,
        '#default_value' => $resource_hints[$index]['resource_hint'],
      );
      $rows[$index]['url'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#title_display' => 'invisible',
        '#default_value' => $resource_hints[$index]['url'],
      );
      $delete_url = \Drupal::service('path.validator')->getUrlIfValid(CALIBR8_PERFORMANCE_SETTINGS_PATH . '/delete_resourcehint/' . $index);
      if($delete_url) {
        $rows[$index]['delete_button'] = array(
          '#type' => 'link',
          '#title' => $this->t('Remove'),
          '#url' => $delete_url,
        );
      } else {
        $rows[$index]['delete_button'] = [
          '#type' => 'markup',
          '#markup' => '',
        ];
      }
      $rows[$index]['#weight'] = $resource_hints[$index]['weight'];
      $rows[$index]['weight'] = array(
        '#type' => 'weight',
        '#title' => $this->t('Weight'),
        '#title_display' => 'invisible',
        '#default_value' => $resource_hints[$index]['weight'],
        '#attributes' => array('class' => array('table-order-weight')),
      );
    }

    if($rows) {
      // Do our own sorting, because drupal does not seem to do this.
      uasort($rows, '\Drupal\Component\Utility\SortArray::sortByWeightProperty');
    } else {
      unset($form['resource_hints_table']['#tabledrag']);
      $rows[] = [
        '#attributes' => array('class' => array('not-draggable')),
        'title' => [
          '#markup' => '<em>' . $this->t('No resource hints added yet.') . '</em>',
          '#wrapper_attributes' => array(
            'colspan' => count($form['resource_hints_table']['#header']),
          ),
        ],
      ];
    }

    // Add resource hint form
    $rows[] = [
      '#attributes' => array('class' => array('not-draggable')),
      'title' => [
      '#markup' => '<strong>' . $this->t('Add resource hint') . '</strong>',
        '#wrapper_attributes' => array(
          'colspan' => count($form['resource_hints_table']['#header']),
        ),
      ]
    ];
    $rows[] = [
      '#attributes' => array('class' => array('not-draggable')),
      'resource_hint' => [
        '#type' => 'select',
        '#title' => $this->t('Resource hint'),
        '#title_display' => 'invisible',
        '#options' => $resource_hints_values,
      ],
      'url' => [
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#title_display' => 'invisible',
      ],
      'actions' => [
        '#type' => 'submit',
        '#value' => $this->t('Add'),
      ],
      'weight' => [
        '#type' => 'weight',
        '#title' => $this->t('Weight'),
        '#title_display' => 'invisible',
        // '#attributes' => array('class' => array('table-order-weight')),
      ],
    ];

    $form['resource_hints_table'] = array_merge($form['resource_hints_table'], $rows);

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

    $resource_hints_table = $form_state->getValue('resource_hints_table');
    $resource_hints_values = [];
    foreach($resource_hints_table as $index => $row) {
      if($row['resource_hint'] && $row['url']) {
        $resource_hints_values[] = [
          'resource_hint' => $row['resource_hint'],
          'url' => $row['url'],
          'weight' => $row['weight'],
        ];
      }
    }
    $config->set('resource_hints', $resource_hints_values)->save();

    parent::submitForm($form, $form_state);
  }

}