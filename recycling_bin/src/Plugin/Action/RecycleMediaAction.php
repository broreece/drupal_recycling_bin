<?php

namespace Drupal\recycling_bin\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom action that checks each node, if the content type can be recycled sets it in the recycling bin.
 *
 * @Action(
 *   id = "recycle_media_action",
 *   label = @Translation("Delete media"),
 * )
 */
class RecycleMediaAction extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Constructs a RecycleNodeAction object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    // If node can be recycled place it in recycling bin, otherwise delete it.
    if ($entity->hasField('in_recycle')) {
      $entity->set('in_recycle', TRUE)->save();
      $message = $entity->label() . ' has been placed in the recycling bin';
    }
    else {
      $entity->delete();
      $message = $entity->label() . ' has been deleted';
    }
    $this->logger->get('media')->notice($message);
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $objects) {
    foreach ($objects as $object) {
      $this->execute($object);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->access('delete', $account)) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

}
