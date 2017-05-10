<?php
/**
 * Created by PhpStorm.
 * User: ihorkruchynenko
 * Date: 07/05/2017
 * Time: 16:03
 */

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\newAdOfferDetails;
use AppBundle\Form\newAdOfferType;
use AppBundle\Entity\PartnerDetails;
use AppBundle\Form\newPartnerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use DateTime;

class AdvertiserController extends Controller
{

    /**
     * @Route("/adsdashboard/{slug}", name="adsdashboard", defaults={"slug" = false}, requirements={"slug": "\d+"})
     */
    public function adDashboardAction (Request $request, $slug) {
        return $this->render('BackEnd/Advertiser/adMasterDash.html.twig');
    }

    /**
     * @Route("/adsoffersdashboard/{slug}", name="adsoffersdashboard", defaults={"slug" = false}, requirements={"slug": "\d+"})
     */
    public function adsoffersdashboardAction (Request $request, $slug) {
        return $this->render('BackEnd/Advertiser/adOfferDash.html.twig');
    }

    /**
     * @Route("/adoffers", name="adoffers")
     */
    public function adOfferAction (Request $request) {
        $tabledata = $this->getDoctrine()->getRepository('AppBundle:newAdOfferDetails')->offerDetailsTable();//getting data for table
        return $this->render('BackEnd/Advertiser/adOffers.html.twig', [
            'tabledata' =>$tabledata
        ]);
    }

    /**
     * @Route("/newadnetwork", name="newadnetwork")
     */
    public function newAdNetworkAction (Request $request) {
        $em = $this ->getDoctrine() ->getManager();
        $newPartner = new PartnerDetails();
        $form = $this->createForm(newPartnerType::class, $newPartner, [
            'action' => $this -> generateUrl('newadnetwork'),
            'method' => 'POST'
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $partnername = $form['partner_name']->getData();
            $partnertype = $form['partner_type']->getData();
            $traffictype = $form['traffic_type']->getData();
            $geo = $form['geo']->getData();
            $size = $form['size']->getData();
            $tire = $form['tire']->getData();
            $newPartner ->setPartnerName($partnername);
            $newPartner ->setPartnerType($partnertype);
            $newPartner ->setTrafficType($traffictype);
            $newPartner ->setGeo($geo);
            $newPartner ->setSize($size);
            $newPartner ->setTire($tire);
            $newPartner ->setDateCreated(new DateTime());
            $em->persist($newPartner);
            $em->flush();
            $tabledata = $this->getDoctrine()->getRepository('AppBundle:PartnerDetails')->adnetworkDetailsTable();//getting data for table
            return $this->render('BackEnd/Advertiser/newAdNetwork.html.twig',[
                'form'=>$form->createView(),
                'tabledata'=>$tabledata
            ]);
        }
        $tabledata = $this->getDoctrine()->getRepository('AppBundle:PartnerDetails')->adnetworkDetailsTable();//getting data for table
        return $this->render('BackEnd/Advertiser/newAdNetwork.html.twig',[
            'form'=>$form->createView(),
            'tabledata'=>$tabledata
        ]);
    }

    /**
     * @Route("/newoffer", name="newoffer")
     */
    public function newOfferAction (Request $request) {
        $em = $this ->getDoctrine() ->getManager();
        $newAdOffer = new newAdOfferDetails();
        $form = $this->createForm(newAdOfferType::class, $newAdOffer, [
            'action' => $this -> generateUrl('newoffer'),
            'method' => 'POST'
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $offername = $form['offer_name']->getData();
            $partnername = $form['partner']->getData();
            $url = $form['url']->getData();
            $geo = $form['geo']->getData();
            $desc = $form['offer_desc']->getData();
            $status = $form['offer_status']->getData();
            $newAdOffer ->setOfferName($offername);
            $newAdOffer ->setPartner($partnername);
            $newAdOffer ->setUrl($url);
            $newAdOffer ->setGeo($geo);
            $newAdOffer ->setOfferDesc($desc);
            $newAdOffer ->setOfferStatus($status);
            $newAdOffer ->setDateModified(new DateTime());
            $em->persist($newAdOffer);
            $em->flush();
            return $this->render('BackEnd/Advertiser/newAdOffer.html.twig',[
                'form'=>$form->createView()
            ]);
        }
        return $this->render('BackEnd/Advertiser/newAdOffer.html.twig',[
            'form'=>$form->createView()
        ]);
    }

}