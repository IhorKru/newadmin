<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SubscribersRepository extends EntityRepository
{
    public function findMaxRow() {
        return $this->getEntityManager()
            ->createQuery('SELECT max(s.id) FROM AppBundle:Subscribers s')
            ->getSingleScalarResult();
    }
    public function emailsSentPeriod() {
        return $this->getEntityManager()
            ->createQuery('SELECT COUNT(s.id)
                                FROM AppBundle:Subscribers s, AppBundle:Lists l, AppBundle:Campaigns c 
                                WHERE 
                                    s.list = l.id 
                                    AND l.id = c.lists 
                                    AND DATE_FORMAT(FROM_UNIXTIME(c.sendDate), \'%e-%b-%Y\') = DATE_FORMAT(now(), \'%e-%b-%Y\')
                                    OR DATE_FORMAT(FROM_UNIXTIME(c.sent), \'%e-%b-%Y\') = DATE_FORMAT(now(), \'%e-%b-%Y\')
                                ')
            ->getSingleScalarResult();
    }

}