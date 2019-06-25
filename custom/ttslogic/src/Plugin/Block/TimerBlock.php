<?php

namespace Drupal\ttslogic\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides a 'Timer' Block.
 *
 * @Block(
 *   id = "timer_custom_block",
 *   admin_label = @Translation("Timer block"),
 *   category = @Translation("Custom"),
 * )
 */
class TimerBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $variables['library'][] = 'ttslogic/timerjquery';
    $current_url = Url::fromRoute('<current>');
    $path = $current_url->getInternalPath();
    $path_args = explode('/', $path);
    $arg0 = $path_args[0] ?? NULL;
    $arg1 = $path_args[1] ?? NULL;
    $duration = 0;
    if ($arg0 == 'group' && !empty($arg1) && isset($path_args[2]) && $path_args[2] == 'module') {
      $group = \Drupal\group\Entity\Group::load($arg1);
      if ($group) {
        $duration = $group->get("field_learning_path_duration")->getValue();
        $duration = $duration[0]["value"];
      }
    }
    $duration = explode(' ', $duration);
    $variables['drupalSettings']['ttslogic']['timer'] = $duration[0];
    $variables['drupalSettings']['ttslogic']['group_id'] = $arg1;
    return array(
      '#markup' => $duration[0],
      '#attached' => $variables,
      '#cache' => ['max-age' => 0],
    );
  }

}