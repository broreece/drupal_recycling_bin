<?php

namespace Drupal\recycling_bin\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration form for the recycling bin module.
 */
class RecyclingBinConfigForm extends ConfigFormBase {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['recycling_bin.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'recycling_bin.settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $default_config = $this->configFactory()->get('recycling_bin.settings');

    $form['#title'] = $this->t('Recycling bin configurations');

    // Allow user to select content types that can be recycled.
    $form['node_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content types'),
    ];
    $node_types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    foreach ($node_types as $content_type) {
      $form['node_types']['#options'][$content_type->id()] = t($content_type->label());
    }
    $form['node_types']['#default_value'] = $default_config->get('node_types') ?: [];

    // Allow user to select media types that can be recycled.
    $form['media_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Media types'),
    ];
    $media_types = $this->entityTypeManager->getStorage('media_type')->loadMultiple();
    foreach ($media_types as $media_type) {
      $form['media_types']['#options'][$media_type->id()] = t($media_type->label());
    }
    $form['media_types']['#default_value'] = $default_config->get('media_types') ?: [];

    $form['clear_frequency'] = [
      '#type' => 'select',
      '#title' => t('Clear frequency'),
      '#options' => [
        'never' => t('Never'),
        '86400' => t('Every Day'),
        '604800' => t('Every Week'),
        '2592000' => t('Every 30 days'),
      ],
      '#default_value' => $default_config->get('clear_frequency'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Submit form and save config.
    parent::submitForm($form, $form_state);
    $this->configFactory()->getEditable('recycling_bin.settings')
      ->setData($form_state->cleanValues()->getValues())
      ->save();
    // Load all selected types and load the in_recycle field.
    $types = [
      'node',
      'media',
    ];
    foreach ($types as $entity_type) {
      $string = $entity_type . '_types';
      $results = $form_state->getValue($entity_type . '_types');
      foreach ($results as $result => $set) {
        $field = FieldConfig::loadByName($entity_type, $result, 'in_recycle');
        // If the content was set in the config and the field does not exist.
        if ($set) {
          if (empty($field)) {
            // Load storage, check if it exists, if not create instance of it.
            $field_storage = FieldStorageConfig::loadByName($entity_type, 'in_recycle');
            if (empty($field_storage)) {
              $field_storage = FieldStorageConfig::create([
                'field_name' => 'in_recycle',
                'entity_type' => $entity_type,
                'type' => 'boolean',
              ])->save();
            }
            $field = FieldConfig::create([
              'field_storage' => $field_storage,
              'bundle' => $result,
            ]);
            $field->save();
          }
        }
        // If node was not set check if the field exists, if so delete the field.
        else {
          if (!empty($field)) {
            $field->delete();
            // If last field is deleted we need to keep storage to prevent errors.
            $field_storage = FieldStorageConfig::loadByName($entity_type, 'in_recycle');
            if (empty($field_storage)) {
              FieldStorageConfig::create([
                'field_name' => 'in_recycle',
                'entity_type' => $entity_type,
                'type' => 'boolean',
              ])->save();
            }
          }
        }
      }
    }
  }

}
