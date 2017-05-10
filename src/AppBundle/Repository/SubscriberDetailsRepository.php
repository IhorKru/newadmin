<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SubscriberDetailsRepository extends EntityRepository
{
    public function campEligibilityCalc($numcampaigns) {
        //$em = $this ->getDoctrine() ->getManager();
        ####selecting users that did not opt out from mailing resource
        $qb0 = $this->getEntityManager()->createQueryBuilder();
        $qb0
            -> select('opto')
            -> from('AppBundle\Entity\SubscriberOptOutDetails', 'opto')
            -> where('s.id = opto.user')
        ;
        ####selecting users that did not get email from us for 7 days + did not ever bounce hard + did not ever complaint
        $qb1 = $this->getEntityManager()->createQueryBuilder();
        $qb1
            ->select('send')
            ->from('AppBundle\Entity\Subscribers', 'send')
            ->where('DATE_FORMAT(now(), \'%e-%b-%Y\') - DATE_FORMAT(FROM_UNIXTIME(send.last_campaign), \'%e-%b-%Y\') <=7')
            ->andwhere('send.bounced <> 1')
            ->andwhere('send.complaint <> 1')
            ->andwhere('s.emailaddress = send.emailaddress');
        ####selecting users that did not end up in ADK campaign error list
        $qb2 = $this->getEntityManager()->createQueryBuilder();
        $qb2
            -> select('adkerr')
            -> from('AppBundle\Entity\SubscriberADKCampErrors', 'adkerr')
            -> andwhere('s.emailaddress = adkerr.recipient')
        ;
        ####primary query to select users for future campaigns
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('s')
            -> from('AppBundle\Entity\SubscriberDetails', 's')
            -> where($qb->expr()->not($qb->expr()->exists($qb0->getDQL())))
            -> andWhere($qb->expr()->not($qb->expr()->exists($qb1->getDQL())))
            -> andWhere($qb->expr()->not($qb->expr()->exists($qb2->getDQL())))
            -> setMaxResults($numcampaigns)
        ;
        $subscribers = $qb->getQuery()->getResult();
        return $subscribers;
    }
}
