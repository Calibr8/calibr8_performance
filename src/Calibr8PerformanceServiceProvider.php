<?php

namespace Drupal\calibr8_performance;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the language manager service.
 */
class Calibr8PerformanceServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('asset.css.collection_renderer');
    $definition->setClass('Drupal\calibr8_performance\Core\Calibr8CssCollectionRenderer');
  }

}