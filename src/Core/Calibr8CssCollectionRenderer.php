<?php

namespace Drupal\calibr8_performance\Core;

use Drupal\Component\Utility\Html;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Drupal\Core\Asset\CssCollectionRenderer;

/**
 * Renders CSS assets.
 */
class Calibr8CssCollectionRenderer extends CssCollectionRenderer implements AssetCollectionRendererInterface {

  /**
   * The state key/value store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a CssCollectionRenderer.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key/value store.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function render(array $css_assets) {

    // Return default elements if disabled on performance settings page.
    $config = \Drupal::service('config.factory')->getEditable('calibr8_performance.settings');
    $replace_css_imports = $config->get('replace_css_imports');
    if(!$replace_css_imports) {
      // Get default rendering
      $elements = parent::render($css_assets);
      return $elements;
    }

    // A dummy query-string is added to filenames, to gain control over
    // browser-caching. The string changes on every update or full cache
    // flush, forcing browsers to load a new copy of the files, as the
    // URL changed.
    $query_string = $this->state->get('system.css_js_query_string') ?: '0';

    // Defaults for LINK and STYLE elements.
    $link_element_defaults = [
      '#type' => 'html_tag',
      '#tag' => 'link',
      '#attributes' => [
        'rel' => 'stylesheet',
        'type' =>'text/css',
      ],
    ];

    $elements = [];
    foreach($css_assets as $css_asset) {

      $element = $link_element_defaults;

      switch ($css_asset['type']) {
        case 'file':
          $element['#attributes']['href'] = Html::escape(file_url_transform_relative(file_create_url($css_asset['data'])) . '?' . $query_string);
          $element['#attributes']['media'] = $css_asset['media'];
          $element['#browsers'] =  $css_asset['browsers'];
          break;
        case 'external':
          $element = $link_element_defaults;
          $element['#attributes']['href'] = $css_asset['data'];
          $element['#attributes']['media'] = $css_asset['media'];
          $element['#browsers'] = $css_asset['browsers'];
          $elements[] = $element;
          break;
        default:
          throw new \Exception('Invalid CSS asset type.');
      }

      // Add element
      $elements[] = $element;
    }

    return $elements;

  }

}
