<?php

namespace Drupal\recycling_bin\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\recycling_bin\Service\RecyclingBinManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Check if a node is currently in the recycling bin and if the user can edit nodes.
 */
class EditAccessCheck implements AccessInterface {

  /**
   * Current path service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Current user service.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Recycling bin manager service.
   *
   * @var \Drupal\recycling_bin\Service\RecyclingBinManager
   */
  protected $recyclingBinManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(CurrentRouteMatch $currentRouteMatch, AccountProxyInterface $currentUser,
                              RecyclingBinManager $recyclingBinManager) {
    $this->currentRouteMatch = $currentRouteMatch;
    $this->currentUser = $currentUser;
    $this->recyclingBinManager = $recyclingBinManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('current_user'),
      $container->get('recycling_bin.manager'),
    );
  }

  /**
   * Checks if current node type is enabled to be recycled, if so returns access denied.
   */
  public function access() {
    $entity = $this->currentRouteMatch->getParameters()->get($this->currentRouteMatch->getParameters()->keys()[0]);
    if ($entity != NULL) {
      if ($this->recyclingBinManager->is_set($entity->bundle()) && $entity->get('in_recycle')->value) {
        return AccessResult::forbidden();
      }
      if ($entity->access('update', $this->currentUser->getAccount())) {
        return AccessResult::allowed();
      }
    }
    return AccessResult::neutral();
  }

}
