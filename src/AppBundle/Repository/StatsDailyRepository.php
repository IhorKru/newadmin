<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class StatsDailyRepository extends EntityRepository
{
    public function calculateStats($table,$where0,$where1,$where2,$where3){
        $em = $this ->getEntityManager();
        //counting total subscribers current period
        $qb0 = $em->createQueryBuilder();
        $qb0
            -> select('s.subscribers')
            -> from($table, 's')
            -> where('s.date = CURRENT_DATE()')
        ;
        $cntSubscribers = $qb0 ->getQuery() ->getSingleScalarResult();
        //counting total subscribers last period
        $qb1 = $em->createQueryBuilder();
        $qb1
            -> select('s.subscribers')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $cntSubscribersLp = $qb1 ->getQuery() ->getSingleScalarResult();
            $ratiototalsubsc = $this->calulateChange($cntSubscribers,$cntSubscribersLp);//% change
        //count subscribers added this period
        $qb2 = $em->createQueryBuilder();
        $qb2
            -> select('s.subscribersperiod')
            -> from($table, 's')
            -> where($where0)
            -> andwhere($where3)
        ;
        $cntSubsAddCp = $qb2 ->getQuery() ->getSingleScalarResult();
        //count subscribers added last period
        $qb3 = $em->createQueryBuilder();
        $qb3
            -> select('s.subscribersperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $cntSubsAddLp = $qb3 ->getQuery() ->getSingleScalarResult();
            $ratiosubscrperiod = $this->calulateChange($cntSubsAddCp, $cntSubsAddLp);//%change
        //count unsubscribers current period
        $qb4 = $em->createQueryBuilder();
        $qb4
            -> select('s.unsubscribersperiod')
            -> from($table, 's')
            -> where($where0)
            -> andwhere($where3)
        ;
        $cntUnsubsAddCp = $qb4 ->getQuery() ->getSingleScalarResult();
        //count unsubscribers last period
        $qb5 = $em->createQueryBuilder();
        $qb5
            -> select('s.unsubscribersperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $cntUnsubsAddLp = $qb5 ->getQuery() ->getSingleScalarResult();
            $ratiounsubscrperiod = $this->calulateChange($cntUnsubsAddCp, $cntUnsubsAddLp);//%change
        //emails sent current period
        $qb6 = $em->createQueryBuilder();
        $qb6
            -> select('s.emailssentperiod')
            -> from($table, 's')
            -> where($where0)
            -> andwhere($where3)
        ;
        $cntEmailsSentCp = $qb6 ->getQuery() ->getSingleScalarResult();
        //emails sent last period
        $qb7 = $em->createQueryBuilder();
        $qb7
            -> select('s.emailssentperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $cntEmailsSentLp = $qb7 ->getQuery() ->getSingleScalarResult();
            $ratioemailssent = $this->calulateChange($cntEmailsSentCp, $cntEmailsSentLp);//%change
        //clicks current period
        $qb8 = $em->createQueryBuilder();
        $qb8
            -> select('s.clicksperiod')
            -> from($table, 's')
            -> where($where0)
            -> andwhere($where3)
        ;
        $cntPaidClicksCp = $qb7 ->getQuery() ->getSingleScalarResult();
        //clicls last period
        $qb9 = $this->getEntityManager()->createQueryBuilder();
        $qb9
            -> select('s.clicksperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $cntPaidClicksLp = $qb9 ->getQuery() ->getSingleScalarResult();
            $ratioclicksperiod = $this->calulateChange($cntPaidClicksCp, $cntPaidClicksLp);//%change
        //revenue current period
        $qb10 = $this->getEntityManager()->createQueryBuilder();
        $qb10
            -> select('s.revenueperiod')
            -> from($table, 's')
            -> where($where0)
            -> andwhere($where3)
        ;
        $revenueCp = $qb10 ->getQuery() ->getSingleScalarResult();
        //revenue last period
        $qb11 = $this->getEntityManager()->createQueryBuilder();
        $qb11
            -> select('s.revenueperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $evenueLp = $qb11 ->getQuery() ->getSingleScalarResult();
        $ratiorevenue = $this->calulateChange($revenueCp, $evenueLp);//%change
        return[
            'countsubscr' => $cntSubscribers,
            'ratiototalsubsc' => $ratiototalsubsc,
            'cntsubscrperiod' => $cntSubsAddCp,
            'rationsubscrperiod' => $ratiosubscrperiod,
            'cntunsubscribers' => $cntUnsubsAddCp,
            'rationsubscrperiod' => $ratiounsubscrperiod,
            'cntemailssentperiod' => $cntEmailsSentCp,
            'ratioemailssent' => $ratioemailssent,
            'cntclicksperiod' => $cntPaidClicksCp,
            'ratioclicksperiod' => $ratioclicksperiod,
            'revenueperiod' => $revenueCp,
            'ratiorevenue' => $ratiorevenue
            ];
    } //calculating data from index page
    public function cntEmailsSentCp($table,$where0, $where3) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.emailssentperiod')
            -> from($table, 's')
            -> where($where0)
            -> andWhere($where3)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of emails, sent this period*/
    public function currentCp($what, $table, $where0) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select($what)
            -> from($table, 's')
            -> where($where0)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//single pull function for campaigns stats dash
    public function historyLp($what, $table) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select($what)
            -> from($table, 's')
            -> orderBy('s.date', 'DESC')
            -> setMaxResults(18);
        ;
        $result = $qb ->getQuery() ->getArrayResult();
        return $result;
    }//concolidated pull function for campaigns dash
    public function campDetailTable() {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('DISTINCT DATE_FORMAT(from_unixtime(c.sent),\'%e-%b-%Y %H:%i:%S\') AS SendDate,
                              DATE_FORMAT(from_unixtime(c.sendDate),\'%e-%b-%Y %H:%i:%S\') AS SendDateF,
                              COUNT(DISTINCT c.id) AS CountCampaigns,
                              COUNT (s.id) AS CountEmails,
                              c.opens AS CountOpens,
                              SUM(s.bounced) AS CountBounces,
                              SUM(s.bounce_soft) AS CountBouncesS,
                              SUM(s.complaint) AS CountComplaints,
                              SUM(lk.clicks) AS CountClicks,
                              0 AS Revenue')
            -> from('AppBundle:Campaigns', 'c')
            -> leftJoin('AppBundle\Entity\Links', 'lk', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.id = lk.campaignId')
            -> leftJoin('AppBundle\Entity\Lists', 'li', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.toSendLists = li.id')
            -> leftJoin('AppBundle\Entity\Subscribers', 's', \Doctrine\ORM\Query\Expr\Join::WITH, 'li.id = s.list')
            -> groupBy('c.sent, c.sendDate')
            -> orderBy('c.sendDate', 'ASC')
        ;
        $result = $qb ->getQuery() ->getResult();
        return $result;

    }//concolidated pull function for campaigns dash
    public function campDetailTable2() {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('DISTINCT ca.datecreated AS DateCreated,
                              COUNT(DISTINCT c.id) AS CountCampaigns,
                              COUNT (s.id) AS CountEmails,
                              c.opens AS CountOpens,
                              SUM(s.bounced) AS CountBounces,
                              SUM(s.bounce_soft) AS CountBouncesS,
                              SUM(s.complaint) AS CountComplaints,
                              SUM(lk.clicks) AS CountClicks,
                              0 AS Revenue')
            -> from('AppBundle:CampaignInputDetails', 'ca')
            -> leftJoin('AppBundle\Entity\Campaigns', 'c', \Doctrine\ORM\Query\Expr\Join::WITH,'ca.id = c.batch_id')
            -> leftJoin('AppBundle\Entity\Links', 'lk', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.id = lk.campaignId')
            -> leftJoin('AppBundle\Entity\Lists', 'li', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.toSendLists = li.id')
            -> leftJoin('AppBundle\Entity\Subscribers', 's', \Doctrine\ORM\Query\Expr\Join::WITH, 'li.id = s.list')
            -> groupBy('ca.datecreated')
            -> orderBy('ca.datecreated', 'ASC')
        ;
        $result = $qb ->getQuery() ->getResult();
        return $result;

    }//concolidated pull function for campaigns dash
    private function calulateChange($param1, $param2) {
        if ($param1 == 0 or $param2 == 0) {
            $change = 0;
        } else {
            $change = ($param1/$param2)*100 - 100;
        }
        return $change;
    } //calculate change between 2 parameters

    /*public function cntSubscribers($table) {
    $qb = $this->getEntityManager()->createQueryBuilder();
    $qb
        -> select('s.subscribers')
        -> from($table, 's')
        -> where('s.date = CURRENT_DATE()')
    ;
    $result = $qb ->getQuery() ->getSingleScalarResult();
    return $result;
}//total count of subscribers*/
    /*public function cntSubscribersLp($table,$where1,$where2) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.subscribers')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of subscribers as off last period*/
    /*public function cntSubsAddCp($table,$where0,$where3) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.subscribersperiod')
            -> from($table, 's')
            -> where($where0)
            -> andwhere($where3)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of subscribers, added in current period*/
    /*public function cntSubsAddLp($table,$where1,$where2) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.subscribersperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of subscribers, added in last period*/
    /*public function cntUnsubsAddCp($table,$where0,$where3) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.unsubscribersperiod')
            -> from($table, 's')
            -> where($where0)
            -> andwhere($where3)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of unsubscribers, added this period*/
    /*public function cntUnsubsAddLp($table,$where1,$where2) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.unsubscribersperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of unsubscribers, added last period*/
    /*public function cntEmailsSentLp($table,$where1,$where2) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.emailssentperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of emails, sent this period*/
    /*public function cntPaidClicksCp($table,$where0,$where3) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.clicksperiod')
            -> from($table, 's')
            -> where($where0)
            -> andwhere($where3)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of paid clicks this period*/
    /*public function cntPaidClicksLp($table,$where1,$where2) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.clicksperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of paid clicks last period*/
    /*public function revenueCp($table,$where0,$where3) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.revenueperiod')
            -> from($table, 's')
            -> where($where0)
            -> andwhere($where3)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//revenue current period*/
    /*public function revenueLp($table,$where1,$where2) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.revenueperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//revenue last period*/
}
