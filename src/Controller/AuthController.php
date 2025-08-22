<?php

namespace App\Controller;

use App\Controller\BaseController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends BaseController
{
  public function __construct(ManagerRegistry $doctrine,
      RequestStack $requestStack) {
    parent::__construct($doctrine, $requestStack);
  }

  #[Route('/auth',
    methods: ['GET']
  )]
  public function authAction() : Response {
    $session = $this->requestStack->getSession();
    $url=$session->get("url");
    if(!isset($url)) {
      return new Response("Access denied", Response::HTTP_FORBIDDEN);
    }
    $session->set("is_auth", True);
    return new RedirectResponse($url);
  }
}
