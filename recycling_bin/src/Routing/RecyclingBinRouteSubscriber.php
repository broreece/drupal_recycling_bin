<?php

namespace Drupal\recycling_bin\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Altering node routes to restrict access to recycled node types and content in the recycling bin.
 */
class RecyclingBinRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Limit access to delete form to only nodes that are not recyclable.
    if ($route = $collection->get('entity.node.delete_form')) {
      $route->setRequirement('_custom_access', 'recycling_bin_access.delete::access');
    }
    // Limit access to edit form to only nodes that are not in the recycling bin.
    else if ($route = $collection->get('entity.node.edit_form')) {
      $route->setRequirement('_custom_access', 'recycling_bin_access.edit::access');
    }
    // Limit access to nodes if they are in the recycling bin.
    else if ($route = $collection->get('entity.node.canonical')) {
      $route->setRequirement('_custom_access', 'recycling_bin_access.general::access');
    }
    // Limit access to edit form to only media that are not in the recycling bin.
    else if ($route = $collection->get('entity.media.delete_form')) {
      $route->setRequirement('_custom_access', 'recycling_bin_access.delete::access');
    }
    // Limit access to edit form to only media that are not in the recycling bin.
    else if ($route = $collection->get('entity.media.edit_form')) {
      $route->setRequirement('_custom_access', 'recycling_bin_access.edit::access');
    }
    // Limit access to media if they are in the recycling bin.
    else if ($route = $collection->get('entity.media.canonical')) {
      $route->setRequirement('_custom_access', 'recycling_bin_access.general::access');
    }
  }

}
