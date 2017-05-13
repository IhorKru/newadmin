<?php
/**
 * Created by PhpStorm.
 * User: ihorkruchynenko
 * Date: 13/05/2017
 * Time: 20:42
 */

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


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

        for ($i = 0; $i < 7; $i++) {//selecting length of the period
            $gperiod = strftime($format, $timestamp);
            $where0 = "s.date = CURRENT_DATE()";
            $emailssent = $em->getRepository('AppBundle:StatsDaily')->cntEmailsSentCp($table,$where0,$where3);//selecting count of emails sent in that specific day
            $emaildata[] = ['period' => $gperiod, 'emailssent' => $emailssent];
            $timestamp = strtotime($addperiod, $timestamp);
        } ////////CALCULATING DATA FROM THE GRAPH/////////*/

        return $this->render('BackEnd/index.html.twig', ['statsdata' => $statsdata, 'period' => $period, 'emaildata' => $emaildata]);

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
        $tabledata = $this->getDoctrine()->getRepository('AppBundle:StatsDaily')->campDetailTable();//getting data for table
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