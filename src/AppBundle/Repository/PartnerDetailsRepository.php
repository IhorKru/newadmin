<?php

namespace AppBundle\Repository;

/**
 * PartnerDetailsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PartnerDetailsRepository extends \Doctrine\ORM\EntityRepository
{
    public function publisherDetailsTable() {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('ad.partner_name as NetworkName,
                              tt.trafficTypeName as TrafficType,
                              ad.geo as Geo,
                              ad.size as Size,
                              tr.tireName as Tire')
            -> from('AppBundle:PartnerDetails', 'ad')
            -> join('ad.trafficType', 'tt')
            -> join('ad.tire', 'tr')
            -> where('ad.partnerType = 1')
        ;
        $result = $qb ->getQuery() ->getResult();
        return $result;

    }//concolidated pull function for campaigns dash

    public function adnetworkDetailsTable() {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            -> select('ad.partner_name as NetworkName,
                              tt.trafficTypeName as TrafficType,
                              ad.geo as Geo,
                              ad.size as Size,
                              tr.tireName as Tire')
            -> from('AppBundle:PartnerDetails', 'ad')
            -> join('ad.trafficType', 'tt')
            -> join('ad.tire', 'tr')
            -> where('ad.partnerType = 2')
        ;
        $result = $qb ->getQuery() ->getResult();
        return $result;

    }//concolidated pull function for campaigns dash
}
