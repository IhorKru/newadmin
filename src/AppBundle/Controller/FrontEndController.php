<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class FrontEndController extends Controller
{
    /**
     * @Route("/", name="index")
     * @return Response
     */
    public function indexAction () {
        return $this->render('FrontEnd/index.html.twig');
    }
}