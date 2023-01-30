<?php

namespace Drupal\recycling_bin\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Form\FormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Restore form for nodes in the recycling bin module.
 */
class RecyclingBinNodeConfirmRestoreForm extends FormBase {

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a RecyclingBinNodeConfirmRestoreForm object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(LoggerChannelFactoryInterface $logger, MessengerInterface $messenger) {
    $this->logger = $logger;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory'),
      $container->get('messenger'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'recycling_bin.node.restore_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Are you sure you want to restore node: ' . $node->label() . '.'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Confirm'),
      '#button_type' => 'primary',
      '#node' => $node,
      '#submit' => ['::submitForm'],
    ];

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#submit' => ['::cancelForm'],
    ];

    return $form;
  }

  /**
   * Function called when the form is submitted, restores the content and returns to recycling bin view.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = $form_state->getTriggeringElement()['#node'];
    $node->set('in_recycle', FALSE);
    $form_state->setRedirect('view.recycling_bin.page_1');

    // Logs activity.
    $message = 'Node: ' . $node->label() . ' has been restored and set to draft.';
    $this->logger->get('content')->notice($message);
    $this->messenger->addStatus($message);
  }

  /**
   * Function called when the form is cancelled, returns to recycling bin view.
   */
  public function cancelForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('view.recycling_bin.page_1');
  }

}
