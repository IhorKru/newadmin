<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class StatsDailyRepository extends EntityRepository
{
    public function cntSubscribers($table) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.subscribers')
            -> from($table, 's')
            -> where('s.date = CURRENT_DATE()')
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//total count of subscribers
    public function cntSubscribersLp($table,$where1,$where2) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.subscribers')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of subscribers as off last period
    public function cntSubsAddCp($table,$where0,$where3) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.subscribersperiod')
            -> from($table, 's')
            -> where($where0)
            -> andwhere($where3)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of subscribers, added in current period
    public function cntSubsAddLp($table,$where1,$where2) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.subscribersperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of subscribers, added in last period
    public function cntUnsubsAddCp($table,$where0,$where3) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.unsubscribersperiod')
            -> from($table, 's')
            -> where($where0)
            -> andwhere($where3)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of unsubscribers, added this period
    public function cntUnsubsAddLp($table,$where1,$where2) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.unsubscribersperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of unsubscribers, added last period
    public function cntEmailsSentCp($table,$where0,$where3) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.emailssentperiod')
            -> from($table, 's')
            -> where($where0)
            -> andwhere($where3)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of emails, sent this period
    public function cntEmailsSentLp($table,$where1,$where2) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.emailssentperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of emails, sent this period
    public function cntPaidClicksCp($table,$where0,$where3) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.clicksperiod')
            -> from($table, 's')
            -> where($where0)
            -> andwhere($where3)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of paid clicks this period
    public function cntPaidClicksLp($table,$where1,$where2) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.clicksperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//count of paid clicks last period
    public function revenueCp($table,$where0,$where3) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.revenueperiod')
            -> from($table, 's')
            -> where($where0)
            -> andwhere($where3)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//revenue current period
    public function revenueLp($table,$where1,$where2) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s.revenueperiod')
            -> from($table, 's')
            -> where($where1)
            -> andwhere($where2)
        ;
        $result = $qb ->getQuery() ->getSingleScalarResult();
        return $result;
    }//revenue last period
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
            -> select('DISTINCT DATE_FORMAT(from_unixtime(c.sent),\'%e-%b-%Y\') AS SendDate,
                              COUNT(c.id) AS CountCampaigns,
                              SUM(c.recipients) AS CountEmails,
                              0 AS CountBounces,
                              0 AS CountComplaints,
                              0 AS CountOpens,
                              0 AS CountClicks,
                              0 AS Spend,
                              0 AS Revenue')
            -> from('AppBundle:Campaigns', 'c')
            -> groupBy('c.sendDate')
            -> orderBy('c.sendDate', 'ASC')
        ;
        $result = $qb ->getQuery() ->getResult();
        return $result;

    }//concolidated pull function for campaigns dash
}
