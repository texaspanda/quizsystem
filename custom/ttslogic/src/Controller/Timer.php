<?php

namespace Drupal\ttslogic\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Timer extends ControllerBase {
  public function content() {
    $url = Url::fromRoute('<front>')->toString();
    $response = new RedirectResponse($url);
    return $response->send();
  }
}