<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CampaignsRepository extends EntityRepository
{
    public function campaignsPerResource(){
        return $this->getEntityManager()
            ->createQuery('SELECT a.appName, count(c.id) AS campaigns 
                                FROM AppBundle:SendyApps a JOIN a.appdetails c 
                                WHERE c.sent = :empty
                                GROUP BY a.appName')
            ->setParameter('empty', '')
            ->getArrayResult();
    }

    public function emailsPerResource() {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('a.appName, count(s.id) as countsubscr')
            -> from('AppBundle\Entity\SendyApps', 'a')
            -> join('AppBundle\Entity\Campaigns', 'c', \Doctrine\ORM\Query\Expr\Join::WITH, 'a.id = c.app')
            -> join('AppBundle\Entity\Lists', 'l', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.lists = l.id')
            -> join('AppBundle\Entity\Subscribers', 's', \Doctrine\ORM\Query\Expr\Join::WITH, 's.list = l.id')
            -> where('c.sent = :empty')
            -> setParameter('empty', '')
            -> groupBy('a.appName')
        ;
        $resourceemails = $qb ->getQuery() ->getArrayResult();
        return $resourceemails;
    }
}
