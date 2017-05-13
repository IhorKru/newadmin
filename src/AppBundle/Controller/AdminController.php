<?php

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

class AdminController extends Controller
{

    /**
     * @Route("/cpccampaign", name="cpccampaign")
     */
    public function cpcCampaignAction(Request $request) {

        return $this->render('BackEnd/cpccampaign.html.twig');
    }

    /**
     * @Route("/campstats", name="campstats")
     * @Method({"GET", "POST"})
     */
    public function campaignStatsAction(Request $request) {
        $em = $this ->getDoctrine() ->getManager();
        //getting campaigns per resource
        $resourcestats = $em->getRepository('AppBundle:Campaigns')->campaignsPerResource();
        //getting emails per resource
        $resourceemails = $em->getRepository('AppBundle:Campaigns')->emailsPerResource();
        //getting emails sent or scheduled to be sent within 24 hours
        $emailsused = $em->getRepository('AppBundle:Subscribers')->emailsSentPeriod();
        //calculate email limit
            $emaillimit = '50000';
            //global limit a day minus what is in the line or already sent today
            if ($emailsused <> 0) {
               $sendlimit = ($emailsused/$emaillimit) * 100;
            } else {
                $sendlimit = 0;
            }
        //responce
        $partner = "Live";
        $response = new Response();
        $response->setContent($this->renderView('BackEnd/campdetails.html.twig',[
            'resourcestats' => $resourcestats,
            'resourceemails' => $resourceemails,
            'partnername' => $partner,
            'emaillimit' => $sendlimit
        ]));
        return $response;
    }

    /**
    * @Route("/newemailtempl", name="newemailtempl")
    */
    public function newemailtemplAction(Request $request){
        $newTemplate = new Template();
        $em = $this ->getDoctrine() ->getManager();
        $form = $this->createForm(NewEmailType::class, $newTemplate, [
            'action' => $this -> generateUrl('newemailtempl'),
            'method' => 'POST'
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $appobj = $form['app']->getData();
                $app = $appobj ->getID();
            $tempname = $form['template_name']->getData();
            $file = $form['htmltext']->getData();
                $htmlText = file_get_contents($file->getPathname());
            $queryli = $em ->createQuery('SELECT MAX(li.id) FROM AppBundle:Template li');
            $newTemplate ->setId($queryli->getSingleScalarResult() + 1);
            $newTemplate ->setUserid('1');
            $newTemplate ->setApp($app);
            $newTemplate ->setTemplateName($tempname);
            $newTemplate ->setHtmlText($htmlText);
            $em->persist($newTemplate);
            $em->flush();
            return $this->render('BackEnd/newemailtempl.html.twig',[
                'form'=>$form->createView()
            ]);
        }
        $tabledata = $this->getDoctrine()->getRepository('AppBundle:Template')->temaplteDetailsTable();//getting data for table
        return $this->render('BackEnd/newemailtempl.html.twig',[
            'form'=>$form->createView(),
            'tabledata'=>$tabledata
        ]);
    }

    /**
     * @Route("/newpubnetwork", name="newpubnetwork")
     */
    public function newadnetworkAction(Request $request){
        $newPartner = new PartnerDetails();
        $em = $this ->getDoctrine() ->getManager();
        $form = $this->createForm(newPartnerType::class, $newPartner, [
            'action' => $this -> generateUrl('newpubnetwork'),
            'method' => 'POST'
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $networkname = $form['partner_name']->getData();
            $traffictype = $form['traffic_type']->getData();
            $geo = $form['geo']->getData();
            $size = $form['size']->getData();
            $tire = $form['tire']->getData();
            $newPartner ->setPartnerName($networkname);
            $newPartner ->setTrafficType($traffictype);
            $newPartner ->setGeo($geo);
            $newPartner ->setSize($size);
            $newPartner ->setTire($tire);
            $newPartner ->setPartnerType("Ad Network");
            $newPartner ->setDateCreated(new DateTime());
            $em->persist($newPartner);
            $em->flush();
            $tabledata = $this->getDoctrine()->getRepository('AppBundle:PartnerDetails')->publisherDetailsTable();//getting data for table
            return $this->render('BackEnd/Publisher/newPubNetwork.html.twig',[
                'form'=>$form->createView(),
                'tabledata'=>$tabledata
            ]);
        }
        $tabledata = $this->getDoctrine()->getRepository('AppBundle:PartnerDetails')->publisherDetailsTable();//getting data for table
        return $this->render('BackEnd/Publisher/newPubNetwork.html.twig',[
            'form'=>$form->createView(),
            'tabledata'=>$tabledata
        ]);
    }

    private function calulateChange($param1, $param2) {
        if ($param1 == 0 or $param2 == 0) {
            $change = 0;
        } else {
            $change = ($param1/$param2)*100 - 100;
        }
        return $change;
    } //calculate change between 2 parameters
    private function setTableProps($slug) {
        if($slug == "daily") { //daily stats
            $table = "AppBundle\Entity\StatsDaily";
            $where0 = "s.date = CURRENT_DATE()";
        } elseif ($slug == "weekly") { //weekly stats
            $table = "AppBundle\Entity\StatsWeekly";
            $where0 = "s.week = week(now(),1)";
        } elseif ($slug == "monthly") { //monthly stats
            $table = "AppBundle\Entity\StatsMonthly";
            $where0 = "s.month = month(now())";
        } elseif ($slug == "yearly") { //yearly stats
            $table = "AppBundle\Entity\StatsYearly";
            $where0 = "s.year = year(now())";
        } else {
            $table = "AppBundle\Entity\StatsWeekly";
            $where0 = "s.week = week(now(),1)";
        }
        return [$table, $where0];
    } //getting details of the table that will be queried
    private function setTablePropsTwo($slug) {
        $currmonth = date("m");
        $currweek = date("W");
        if($slug == "1") { //daily stats
            $table = "AppBundle\Entity\StatsDaily";
            $where0 = "s.date = CURRENT_DATE()";
            $where1 = "s.date = CURRENT_DATE()-1";
            $where2 = "s.id LIKE '%'";
            $where3 = "s.id LIKE '%'";
            $period = 'daily';
            $timestamp = strtotime("last Monday");
            $format = '%d/%m(%a)';
            $addperiod = '+1 day';
        } elseif ($slug == "2") { //weekly stats
            $table = "AppBundle\Entity\StatsWeekly";
            $where0 = "s.week = week(now(),1)";
            if ($currweek == 1) {
                $where1 = "s.week = 52";
                $where2 = "s.year = year(now())-1";
                $where3 = "s.year = year(now())";
            } else {
                $where1 = "s.week = week(now(),1)-1";
                $where2 = "s.year = year(now())";
                $where3 = $where2;
            }
            $period = 'weekly';
            $timestamp = strtotime("-1 month");
            $format = '%W(%Y)';
            $addperiod = '+1 week';
        } elseif ($slug == "3") { //monthly stats
            $table = "AppBundle\Entity\StatsMonthly";
            $where0 = "s.month = month(now())";
            if($currmonth == 1) {
                $where1 = "s.month = 12";
                $where2 = "s.year = year(now())-1";
                $where3 = "s.year = year(now())";
            } else {
                $where1 = "s.month = month(now())-1";
                $where2 = "s.year = year(now())";
                $where3 = $where2;
            }
            $period = 'monthly';
            $timestamp = strtotime("-1 month");
            $format = '%m(%Y)';
            $addperiod = '+1 month';
        } elseif ($slug == "4") { //yearly stats
            $table = "AppBundle\Entity\StatsYearly";
            $where0 = "s.year = year(now())";
            $where1 = "s.year = year(now())-1";
            $where2 = "s.id LIKE '%'";
            $where3 = "s.id LIKE '%'";
            $period = 'annually';
            $timestamp = strtotime("-1 month");
            $format = '%Y';
            $addperiod = '+1 year';
        } else {
            $table = "AppBundle\Entity\StatsWeekly";
            $where0 = "s.week = week(now(),1)";
            if ($currweek == 1) {
                $where1 = "s.week = 52";
                $where2 = "s.year = year(now())-1";
                $where3 = "s.year = year(now())";
            } else {
                $where1 = "s.week = week(now(),1)-1";
                $where2 = "s.year = year(now())";
                $where3 = $where2;
            }
            $period = 'weekly';
            $timestamp = strtotime("-1 month");
            $format = '%m(%Y)';
            $addperiod = '+1 week';
        }

        return [$table,$where0,$where1,$where2,$where3,$period,$timestamp,$format,$addperiod];
    }
}