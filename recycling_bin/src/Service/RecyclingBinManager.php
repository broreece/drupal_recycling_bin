<?php

namespace Drupal\recycling_bin\Service;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Service containing functions used in the recycling bin code.
 */
class RecyclingBinManager {

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Services injected.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Function that returns if any content type has been enabled in the recycling bin config.
   */
  function is_content_active() {
    $content_types = $this->configFactory->get('recycling_bin.settings')->get('node_types');
    if ($content_types != NULL) {
      foreach ($content_types as $content_type => $set) {
        if ($set) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Function that returns if any media type has been enabled in the recycling bin config.
   */
  function is_media_active() {
    $media_types = $this->configFactory->get('recycling_bin.settings')->get('media_types');
    if ($media_types != NULL) {
      foreach ($media_types as $media_type => $set) {
        if ($set) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Function that returns if a specific bundle that can be media or content has been enabled in the recycle bin config.
   */
  function is_set($bundle) {
    $content_types = $this->configFactory->get('recycling_bin.settings')->get('node_types');
    $media_types = $this->configFactory->get('recycling_bin.settings')->get('media_types');
    $set = false;
    if ($content_types != NULL && isset($content_types[$bundle])) {
      $set = ($content_types[$bundle]);
    }
    if ($media_types != NULL && isset($media_types[$bundle])) {
      // Checks if the bundle is either set in content or media types.
      $set = $set || ($media_types[$bundle]);
    }
    return $set;
  }

  /**
   * Function that returns if a specific content type has been enabled in the recycle bin config.
   */
  function is_content_set($bundle) {
    $content_types = $this->configFactory->get('recycling_bin.settings')->get('node_types');
    if ($content_types != NULL && isset($content_types[$bundle])) {
      return ($content_types[$bundle]);
    }
    return FALSE;
  }

  /**
   * Function that returns if a specific media type has been enabled in the recycle bin config.
   */
  function is_media_set($bundle) {
    $media_types = $this->configFactory->get('recycling_bin.settings')->get('media_types');
    if ($media_types != NULL && isset($media_types[$bundle])) {
      return ($media_types[$bundle]);
    }
    return FALSE;
  }

}
