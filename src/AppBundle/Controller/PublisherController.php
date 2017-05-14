<?php
/**
 * Created by PhpStorm.
 * User: ihorkruchynenko
 * Date: 13/05/2017
 * Time: 20:42
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

class PublisherController extends Controller
{
    /**
     * @Route("/{slug}", name="index", defaults={"slug" = false}, requirements={"slug": "\d+"})
     */
    public function indexAction(Request $request, $slug)
    {
        //getting slug value
        $table = $this->setTablePropsTwo($slug)[0];
        $where0 = $this->setTablePropsTwo($slug)[1];
        $where1 = $this->setTablePropsTwo($slug)[2];
        $where2 = $this->setTablePropsTwo($slug)[3];
        $where3 = $this->setTablePropsTwo($slug)[4];
        $period = $this->setTablePropsTwo($slug)[5];
        $timestamp = $this->setTablePropsTwo($slug)[6];
        $format = $this->setTablePropsTwo($slug)[7];
        $addperiod = $this->setTablePropsTwo($slug)[8];
        $em = $this ->getDoctrine() ->getManager(); //getting data for stats
        //getting data for
        $statsdata = $em->getRepository('AppBundle:StatsDaily')->calculateStats($table,$where0,$where1,$where2,$where3);

        //$where0 = strtotime("now");
        //$gperiod=strftime($format,$where0);

        for ($i = 0; $i < 7; $i++) {
            $gperiod=strftime($format,$timestamp);
            if ($slug == 1) {
                $where0 = "s.date = DATE_ADD(CURRENT_DATE(),'-".$i."','DAY')";
            } elseif ($slug == 2) {
                $where0 = "s.week = ".strftime('%W',$timestamp);
            } elseif ($slug == 3) {
                $where0 = "s.month = ".substr(strftime('%m',$timestamp),-1);
                if ($where0 == '12') {
                    $where3 = "s.year = year(now())-1";
                }
            } elseif ($slug == 4) {
                $where0 = "s.year = ".strftime('%G',$timestamp);
            }
            $emailssent=$em->getRepository('AppBundle:StatsDaily')->cntEmailsSentCp($table,$where0,$where3);//selectingcountofemailssentinthatspecificday
            $emaildata[] = ['period'=>$gperiod,'emailssent'=>$emailssent];
            $timestamp = strtotime($addperiod, $timestamp);

        }
        $emaildata = array_reverse($emaildata);
        return $this->render('BackEnd/index.html.twig', ['statsdata' => $statsdata, 'period' => $period, 'emaildata' => $emaildata]);
        //return $emaildata;

    }

    /**
     * @Route("/campaignsdash/{slug}", name="campaignsdash", defaults={"slug" = false})
     */
    public function campaignsdashAction(Request $request, $slug){
        $em = $this ->getDoctrine() ->getManager();
        $revenue = 0;
        $table = $this->setTableProps($slug)[0];
        $where0 = $this->setTableProps($slug)[1];
        $what = 's.batchesperiod';
        $batchesperiod = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->currentCp($what,$table,$where0);//count of batches sent for the period
        $prevbatches = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->historyLp($what,$table);//selecting 18 prev occurances of above data
        $what = 's.campaignsperiod';
        $campaignsperiod = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->currentCp($what,$table,$where0);//count campaigns current period
        $prevcampaigns = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->historyLp($what,$table);//selecting 18 prev occurances of above data
        $what = 's.emailssentperiod';
        $emailsperiod = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->currentCp($what,$table,$where0);//count of emails sent for the period
        $prevemailssent = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->historyLp($what,$table);//selecting 18 prev occurances of above data
        $what = 's.opensperiod';
        $opensperiod = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->currentCp($what,$table,$where0);//count of opens for the period
        $prevopens = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->historyLp($what,$table);//selecting 18 prev occurances of above data
        $what = 's.clicksperiod';
        $clicksperiod = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->currentCp($what,$table,$where0);//count of opens for the period
        $prevclicks = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->historyLp($what,$table);//selecting 18 prev occurances of above data
        $what = 's.bouncesperiod';
        $bouncesperiod = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->currentCp($what,$table,$where0);//count of bounces for the period
        $prevbounces = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->historyLp($what,$table);//selecting 18 prev occurances of above data
        $what = 's.complaintsperiod';
        $complaintsperiod = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->currentCp($what,$table,$where0);//count of complaints for the period
        $prevcomplaints = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->historyLp($what,$table);//selecting 18 prev occurances of above data
        $what = 's.spendperiod';
        $spendperiod = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->currentCp($what,$table,$where0);//count of complaints for the period
        $prevspend = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->historyLp($what,$table);//selecting 18 prev occurances of above data
        $tabledata = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->campDetailTable2();//getting data for table
        //opens period

        //pushing variables to template
        return $this->render('BackEnd/campaignsdash.html.twig',['batchesperiod'=>$batchesperiod,'prevbatches'=>$prevbatches,'campaignsperiod'=>$campaignsperiod,'prevcampaigns'=>$prevcampaigns,
            'emailsperiod'=>$emailsperiod, 'prevemailssent'=>$prevemailssent,'opensperiod'=>$opensperiod,'prevopens'=>$prevopens,'clicksperiod'=>$clicksperiod,'prevclicks'=>$prevclicks,'bouncesperiod'=>$bouncesperiod,
            'prevbounces'=>$prevbounces,'complaintsperiod'=>$complaintsperiod,'prevcomplaints'=>$prevcomplaints,'spendperiod'=>$spendperiod,'prevspend'=>$prevspend,'revenueperiod'=>$revenue, 'tabledata'=>$tabledata,
            'daily'=>$slug,'weekly'=>$slug,'monthly'=>$slug,'yearly'=>$slug]);
    }

    /**
     * @Route("/subscriberdash/{slug}", name="subscriberdash", defaults={"slug" = false})
     */
    public function subscribersdashAction(Request $request, $slug){
        return $this->render('BackEnd/subscriberdash.html.twig',['daily'=>$slug,'weekly'=>$slug,'monthly'=>$slug,'yearly'=>$slug]);
    }

    /**
     * @Route("/partnerdash/{slug}", name="partnerdash", defaults={"slug" = false})
     */
    public function partnerdashAction(Request $request, $slug){
        return $this->render('BackEnd/partnerdash.html.twig',['daily'=>$slug,'weekly'=>$slug,'monthly'=>$slug,'yearly'=>$slug]);
    }

    /**
     * @Route("/emailcampaigns", name="emailcampaigns")
     */
    public function emailcampaignsAction(Request $request)
    {
        //setting up form entity
        $em = $this ->getDoctrine() ->getManager();
        $newCampaign = new CampaignInputDetails();
        $form = $this->createForm(InputType::class, $newCampaign, [
            'action' => $this -> generateUrl('emailcampaigns'),
            'method' => 'POST'
        ]);
        //processing form data
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $partner_obj = $form['partnername']->getData();
            $partner = $partner_obj ->getID();
            $geo = $form['geo']->getData();
            #if partner is not ADK, collect data from below fields
            if ($partner != 4) {
                $app_obj = $form['resourcename']->getData();
                $app_id = $app_obj ->getID();
                $templateid = $form['templatename']->getData();
                $link1 = $form['link1']->getData();
                $link2 = $form['link2']->getData();
            } elseif ($partner == 4) {
                $app_id = '1';
                $templateid = '1';
                $link1 = 'http://adknowledge.com';
                $link2 = 'http://adknowledge.com';
            }
            $numcampaigns = $form['numemails']->getData();
            $timezone = $form['timezone'] ->getData();
            $depdate = $form['datetosend'] ->getData();
            $datedepf = date_create($depdate . ':00');
            //pushing campaign details to db
            $newCampaign ->setPartnername($partner);
            $newCampaign ->setGeo($geo);
            $newCampaign ->setResourcename($app_id);
            $newCampaign ->setTemplatename($templateid);
            $newCampaign ->setLink1($link1);
            $newCampaign ->setLink2($link2);
            $newCampaign ->setTimezone($timezone);
            $newCampaign ->setDatetosend($datedepf);
            $newCampaign ->setDatecreated(new DateTime());
            $em->persist($newCampaign);
            $em->flush();
            //initiating required action based on the partner
            session_write_close();
            if($partner == 4) {
                //closing down current session and progressing with script creation
                $rootDir = getcwd();
                $adk_process = new Process(
                    'php ../bin/console app:adkaction ' . $numcampaigns . ', ' . $timezone . ',' . $depdate
                );
                $adk_process->setWorkingDirectory($rootDir);
                $adk_process->setTimeout(null);
                $adk_process->start();
                if($adk_process->isRunning()){
                    while($adk_process->isRunning()){
                    }
                    if(!$adk_process->isSuccessful())
                        $this->get('session')->getFlashBag()->add('error',"Oops!The process fininished with an error:".$adk_process->getErrorOutput());
                }
            } else {
                $getcampaign = $this->get('gen.campaign');
                $subscriberst = $getcampaign -> ecampServiceAction($geo, $app_id, $templateid, $numcampaigns, $link1, $link2, $timezone, $depdate);
            }
        }
        return $this->render('BackEnd/emailcampaigns.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/bar", name="progbar")
     * @Method({"GET", "POST"})
     */
    public function ajaxProcessAction(Request $request)
    {
        //getting last updated line
        $count = $this->getDoctrine()->getManager()->getRepository('AppBundle:Subscribers')->findMaxRow();
        return new Response($count);
    }

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
            $timestamp = strtotime("Today");
            $format = '%d/%m(%a)';
            $addperiod = '-1 day';
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
            $timestamp = strtotime("This week");
            $format = '%W(%Y)';
            $addperiod = '-1 week';
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
            $timestamp = strtotime("This Month");
            $format = '%m(%Y)';
            $addperiod = '- 1 month';
        } elseif ($slug == "4") { //yearly stats
            $table = "AppBundle\Entity\StatsYearly";
            $where0 = "s.year = year(now())";
            $where1 = "s.year = year(now())-1";
            $where2 = "s.id LIKE '%'";
            $where3 = "s.id LIKE '%'";
            $period = 'annually';
            $timestamp = strtotime("This Year");
            $format = '%Y';
            $addperiod = '-1 year';
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
    } //getting details of the table that will be queried for index dash
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
    } //getting details of the table that will be queried for campaigns dash
}