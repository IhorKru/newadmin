<?php
/**
 * Created by PhpStorm.
 * User: ihor.kruchynenko
 * Date: 23/03/2017
 * Time: 12:17
 */

namespace AppBundle\Services;

use AppBundle\Controller\AdminController;
use AppBundle\Entity\Campaigns;
use AppBundle\Entity\Lists;
use AppBundle\Entity\Subscriber;
use AppBundle\Entity\SubscriberAddress;
use AppBundle\Entity\SubscriberADKCampaign;
use AppBundle\Entity\SubscriberADKCampErrors;
use AppBundle\Entity\SubscriberDetails;
use AppBundle\Entity\SubscriberOptInDetails;
use AppBundle\Entity\SubscriberOptOutDetails;
use AppBundle\Entity\Subscribers;
use AppBundle\Repository\ListsRepository;
use DateTime;
use DoctrineExtensions\Query\Mysql\Date;
use SimpleXMLElement;
use Symfony\Component\DomCrawler\Crawler;

class adkService extends AdminController
{
    private $numcampaigns;
    private $timezone;
    private $depdate;
    public function adkServiceAction($numcampaigns, $timezone, $depdate)
    {
        # 1a. Configuration setup for connecting to ADK
            $list_id = '23413';
            $sub_id = 'test_sub_id';
            $token = '305c7c5be78b5c8dd583312fe20578ac';
            $url = 'http://integrated.adstation.com/1.3';
            $idomain = 'adk.mediaff.com';
            $cdomain = 'adk.mediaff.com';
            $batchSize = 20;
            $start  = date("m/d/Y h:i:s a", time());
            $e = 0;
        # 1b. Definition of entities
            $subscriber = new SubscriberDetails();
            $address = new SubscriberAddress();
            $optindetails = new SubscriberOptInDetails();
            $optoutdetails = new SubscriberOptOutDetails();
            $subscriber ->getAddressdetail() ->add($address);
            $subscriber ->getOptindetails() ->add($optindetails);
            $subscriber ->getOptoutdetails() ->add($optoutdetails);
        # 1c. Definitiaon of variables
            $subscribersB = array();
            $xmlarray = array();
        # 1d. Definition of repositories
            $em = $this ->getDoctrine() ->getManager();
            $ent = $this->getDoctrine() ->getRepository('AppBundle:SubscriberDetails');
            $templaterepo = $this->getDoctrine()->getRepository('AppBundle:Template');
            $adkcategoryrepo = $this->getDoctrine() ->getRepository('AppBundle:RefADKCategoryDetails');
            $sendyappdetails = $this->getDoctrine() ->getRepository('AppBundle:SendyApps');
        # 2a. Selecting users, eligible for email campaign
            # which are:
            # subscribers that did not opt out
            # subscribers that did not receive email from Mediaff for the last 7 days
            # subscribers that never received email from mediaff
        $subscribers = $this->getDoctrine()->getRepository('AppBundle:SubscriberDetails')->campEligibilityCalc($numcampaigns);
        #2b crunching big array of data onto arrays of 500 emails as per ADK requirement
            if (is_array($subscribers)) {
                $subscribersB = array_chunk($subscribers, 500, true);
            } # creating array of api requests to ADK
            if(is_array($subscribersB)) {
                #processing first level batch
                foreach ($subscribersB as $subscriberBS) {
                    $xml = '';
                    $query = '';
                    foreach ($subscriberBS as $subscriber) {
                        $email = $subscriber ->getEmailaddress();
                        $gender = $subscriber ->getGender();
                        $isocountry = $address ->getIsocountrycode();
                        $metrocode = $address ->getRefgeoid();
                        $state = $address ->getCity();
                        $postalcode = $address ->getPostalcode();

                        $md5_email = md5(strtolower($email));
                        $email_hashes[$md5_email] = $email;
                        $email_domain = strtolower(preg_replace('/.*\@/','',$email));
                        $xml .= "<email>"
                            . "<recipient>$md5_email</recipient>"
                            . "<list>$list_id</list>"
                            . "<domain>$email_domain</domain>"
                            . "<countrycode>$isocountry</countrycode>"
                            . "<metrocode>$metrocode</metrocode>"
                            . "<postalcode>$postalcode</postalcode>"
                            . "<gender>$gender</gender>"
                            . "<test>0</test>"
                            . "</email>";
                    }
                    #preparing xml string
                    $xml = '<request>' . $xml . '</request>';
                    $request = urlencode($xml);
                    $query  = 'Accept-Encoding: gzip' .'&';
                    $query .= 'token=' . $token . '&';
                    $query .= 'subid=' . $sub_id . '&';
                    $query .= 'idomain=' . $idomain . '&';
                    $query .= 'cdomain=' . $cdomain . '&';
                    $query .= 'request=' . $request .'&';
                    $query .= 'test=1';

                    array_push($xmlarray, $query);
                }
            }
        #3a. Sending data to ADK
            $curly = array(); // array of curl handles
            $result = array(); // data to be returned
            $mh = curl_multi_init(); // multi handle
            foreach ($xmlarray as $id => $d) {
                $curly[$id] = curl_init();
                curl_setopt($curly[$id], CURLOPT_URL, $url);
                curl_setopt($curly[$id], CURLOPT_POST, true);
                curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d);
                curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curly[$id], CURLOPT_TIMEOUT, 60);
                curl_setopt($curly[$id], CURLOPT_SSLVERSION, 3);
                curl_multi_add_handle($mh, $curly[$id]);
            } // query data for each of sub queries on the $xmlarray
            $running = null; // execute the handles
            do {
                curl_multi_exec($mh, $running);
            } while($running > 0);
            foreach($curly as $id => $c) {
                $result[$id] = curl_multi_getcontent($c);
                curl_multi_remove_handle($mh, $c);
            }// get content and remove handles
            curl_multi_close($mh);
        #3b. Parsing xml responce
        try {
            $xmlresponse = array();
            $xmlerror = array();
            $subcreative = array();
            $querybatch = $em ->createQuery('SELECT MAX(c.batch_id) FROM AppBundle:Campaigns c');
            $curbatch = $querybatch->getSingleScalarResult() + 1;
            //going into each specific details
            foreach ($result as $id => $xmlbatch) {
                $xml = new SimpleXMLElement($xmlbatch);
                #parsing errors
                foreach ($xml->error as $xmlerror) {
                    if(isset($xmlerror ->recipient)) {
                        $errornum = $xmlerror->num;
                        $errordesc = $xmlerror->str;
                        $requestid = $xmlerror->requestid;
                        $errorrecipient = $email_hashes[ (string)$xmlerror->recipient ];
                        #pusshing errors to db
                        $errordetails = new SubscriberADKCampErrors();
                        $errordetails ->setErrornum($errornum);
                        $errordetails ->setErrordesc($errordesc);
                        $errordetails ->setRequestid($requestid);
                        $errordetails ->setRecipient($errorrecipient);
                        $errordetails ->setDatemodified(new DateTime());
                        $em->persist($errordetails);
                        $em->flush();
                    }
                }
                $em->flush(); //Persist objects that did not make up an entire batch
                $em->clear();
                #parsing valid responces
                    #extracting all category ids from xml doc
                    $categoriesarray = array();
                    foreach ($xml->email as $xmlresponse) {
                        $categoryid = $xmlresponse ->categoryid;
                        if(!in_array((string)$categoryid, $categoriesarray)){
                            array_push($categoriesarray,(string)$categoryid);
                        }
                    }
                    #extracting data from xml based on the category id
                    foreach ($categoriesarray as $category) {
                        foreach ($xml->email as $xmlresponse) {
                            $categoryid = $xmlresponse ->categoryid;
                            if((string)$categoryid == $category) {
                                #creating campaign details
                                foreach($xmlresponse->creative as $creative) {
                                    $sendyfrom = $creative->friendlyfrom;
                                    $friendlyfrom = $creative->friendlyfrom;
                                    $sendytitle = $creative->subject;
                                    $body = $creative->body;
                                }
                                $adkcategory = $adkcategoryrepo->findOneBy(['categoryid' => $categoryid]);
                                if(is_null($adkcategory)) {
                                    $app = '8';
                                } else {
                                    $app = $adkcategory ->getAppId();
                                }
                                $appdetails = $sendyappdetails->findOneBy(['id' => $app]);
                                $appname = $appdetails->getAppName();
                                $appfromname = $appdetails ->getFromName();
                                $appfromemail = $appdetails ->getFromEmail();
                                $appreplytoemail = $appdetails ->getReplyTo();
                                    #creating subscriber lists
                                $newList = new Lists();
                                $newList ->setUserid('1');
                                $newList ->setApp($app);
                                $newList ->setName($sendyfrom);
                                $newList ->setOptIn('1');
                                $newList ->setConfirmUrl('http://mediaff.com');
                                $newList ->setThankyou('0');
                                $newList ->setGoodbye('0');
                                $newList ->setUnsubscribeAllList('1');
                                $newList ->setPrevCount('0');
                                $newList ->setCurrentlyProcessing('0');
                                $newList ->setTotalRecords('0');
                                $em->persist($newList); //persisting data to list table
                                $em->flush($newList);
                                $latestlist = new Lists();
                                $latestlist = $em->getRepository('AppBundle:Lists')->selectLatestList();
                                    #selecting subscribers
                                foreach ($xml->email as $xmlresponse) {
                                    $categoryid = $xmlresponse ->categoryid;
                                    if((string)$categoryid == $category) {
                                        $adksubscremail = $email_hashes[(string)$xmlresponse->recipient];
                                        $subscriber = $ent->findOneByEmailaddress($adksubscremail);
                                        $firstname = $subscriber ->getFirstName();
                                        $lastname = $subscriber ->getLastName();
                                        $subscriptiondate = $optindetails ->getOptindate();
                                        #setting properties for each subscriber
                                        $sendySubscriber = new Subscribers();
                                        $sendySubscriber ->setUserid('1');
                                        $sendySubscriber ->setEmailaddress($adksubscremail);
                                        $sendySubscriber ->setName($firstname);
                                        $sendySubscriber ->setCustomFields($lastname.'%s%');
                                        $sendySubscriber ->setList($latestlist[0]);
                                        $sendySubscriber ->setUnsubscribed('0');
                                        $sendySubscriber ->setBounced('0');
                                        $sendySubscriber ->setBounceSoft('0');
                                        $sendySubscriber ->setComplaint('0');
                                        $sendySubscriber ->setLastCampaign(strtotime($depdate));
                                        $sendySubscriber ->setTimestamp(new DateTime());
                                        $sendySubscriber ->setJoinDate($subscriptiondate);
                                        $sendySubscriber ->setConfirmed('1');
                                        $sendySubscriber ->setMessageID('testmessage');
                                        $em->persist($sendySubscriber);
                                    }
                                }
                                    #pusshing campaign details into DB
                                $sendyoffer = new Campaigns();
                                $sendyoffer ->setUserid('1');
                                $sendyoffer ->setApp($app);
                                $sendyoffer ->setFromName($appfromname);
                                $sendyoffer ->setFromEmail($appfromemail);
                                $sendyoffer ->setReplyTo($appreplytoemail);
                                $sendyoffer ->setTitle("[Name,fallback=], ".$sendytitle);
                                $sendyoffer ->setHtmlText($body);
                                $sendyoffer ->setToSendLists($latestlist[0]->getId());
                                $sendyoffer ->setWysiwyg('1');
                                $sendyoffer ->setLists($latestlist[0]->getId());
                                $sendyoffer ->setSendDate(strtotime($depdate));
                                $sendyoffer ->setTimezone($timezone);
                                $sendyoffer ->setBatchId($curbatch);
                                $em->persist($sendyoffer); //persisting data to campaign table
                                $em->flush(); //pushing data to db
                                break;
                            }
                        }
                    }
            }
        } catch (Exception $e) {

        } catch (ErrorException $er) {

        } catch (Error $ce) {

        }
        /*$end  = date("m/d/Y h:i:s a", time());
        $timelapsed = strtotime($end) - strtotime($start);
        echo $timelapsed;
        return $timelapsed;*/
    }
}