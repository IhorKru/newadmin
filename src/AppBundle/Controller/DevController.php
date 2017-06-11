<?php
/**
 * Created by PhpStorm.
 * User: ihorkruchynenko
 * Date: 18/05/2017
 * Time: 17:01
 */

namespace AppBundle\Controller;
use AppBundle\AppBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\CampaignInputDetails;
use AppBundle\Entity\Template;
use AppBundle\Entity\cpcInputDetails;
use AppBundle\Entity\PartnerDetails;
use AppBundle\Form\InputType;
use AppBundle\Form\cpcInputType;
use AppBundle\Form\NewEmailType;
use AppBundle\Form\newPartnerType;
use Symfony\Component\Process\Process;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use DateTime;

class DevController Extends Controller
{
    /**
     * @Route("/fbrawdata", name="fbrawdata")
     */
    public function facebookAction(){
        return $this->render('Testing/testing.html.twig');
    }
}