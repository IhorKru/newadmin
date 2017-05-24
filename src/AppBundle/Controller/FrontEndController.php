<?php

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use DateTime;

class FrontEndController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction (Request $request) {
        return $this->render('FrontEnd/index.html.twig');
    }
}