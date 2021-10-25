<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class AuthSubscriber implements EventSubscriberInterface
{
  public function __construct() {
  }

  public function onKernelController(ControllerEvent $event): void {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if(isset($user_agent)) {
      if(stripos($user_agent, "Bot") !== false) {
        throw new AccessDeniedHttpException('Access denied');
      }
    }

    $controller = $event->getController();

    // when a controller class defines multiple action methods, the controller
    // is returned as [$controllerInstance, 'methodName']
    if (is_array($controller)) {
      $controller = $controller[0];
    }

    if ($controller instanceof \App\Controller\BaseController) {
      if (!($controller instanceof \App\Controller\AuthController)) {
        $session = $controller->requestStack->getSession();

        $is_auth = $session->get("is_auth");
        if(!isset($is_auth)) {
          $session->set("url", $_SERVER['REQUEST_URI']);
          $session->set("ip", $_SERVER['REMOTE_ADDR']);
          $html = <<<EOF
          <script>
          a=document;b="a";a.location="/"+b+'uth'
          </script>
          EOF;
          echo $html;
          exit;
        }
      }
    }
  }

  public static function getSubscribedEvents(): array {
    return [ KernelEvents::CONTROLLER => 'onKernelController' ];
  }
}
