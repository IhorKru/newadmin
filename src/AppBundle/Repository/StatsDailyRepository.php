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

}
