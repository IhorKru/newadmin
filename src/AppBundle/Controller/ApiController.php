<?php
/**
 * Created by PhpStorm.
 * User: ihorkruchynenko
 * Date: 27/05/2017
 * Time: 22:08
 */

namespace AppBundle\Controller;
use React\Http\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ApiController extends Controller
{
    //identify request (collect all requests comming into api
    //extract parameters from the request
        //partnerid
        //offerid
        //reade request header
            //geo
            //device type
    //find offer based on offer id
    //find partner based on partner id
    //check if all is matching from the header
    //record a click
    //redirect to offer
    /**
     * @Route("/api")
     */
    public function apiAction (Request $request) {
        //select url to be redirected to
    }

}