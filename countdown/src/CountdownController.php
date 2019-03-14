<?php

namespace Drupal\countdown;

use Drupal\Core\Controller\ControllerBase;

/**
 * Defines CountdownController class.
 */
class CountdownController extends ControllerBase {
  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Counting the days until event starts or notifying its status!'),
    ];
  }
}
