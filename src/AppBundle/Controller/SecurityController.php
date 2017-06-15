<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(Request $request)
    {

        $authenticationUtils = $this->get('security.authentication_utils');
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        #creating login userform
            $newLogin = new User();
            $form = $this->createForm(LoginType::class, $newLogin, [
                'action' => $this -> generateUrl('login'),
                'method' => 'POST'
            ]);
            $form->handleRequest($request);

        return $this->render('BackEnd/login.html.twig',[
            'form'=>$form->createView(),
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }
}