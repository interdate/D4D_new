<?php

namespace AppBundle\Controller\Frontend;

use AppBundle\entity\Communication;
use AppBundle\Services\Messenger\Chat;
use AppBundle\Services\Messenger\Dialog;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

session_write_close();

class MessengerController extends Controller
{

    /**
     * @Route("/user/messenger", name="messenger")
     */
    public function indexAction()
    {
        return $this->render('frontend/user/messenger/index.html.twig', array(
            'messages' => $this->getDoctrine()->getRepository('AppBundle:User')->getDialogs($this->getUser()),
            'points' => $this->getUser()->getPoints()
        ));
    }

    /**
     * @Route("/user/messenger/dialog/open/userId:{userId}/contactId:{contactId}", name="messenger_dialog_open")
     */
    public function openDialogAction($userId, $contactId)
    {
        if ($userId != $this->getUser()->getId() or $userId == $contactId) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        $dialog = new Dialog(array(
            'userId' => $userId,
            'contactId' => $contactId,
        ));

        $usersRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
        $userWith = $usersRepo->find($contactId);
        $allowedToSent = $usersRepo->isAllowedToSend($this->getUser(), $userWith);
        if ($allowedToSent === '1') {
            $blacklist = 'הנך ברשימת החסומים של משתמש זה. לא ניתן לשלוח הודעה.';
        } else {
            $blacklist = '';
            if ($allowedToSent === '2') {
                $blacklist = 'משתמש זה הגדיר שאינך יכול לפנות אליו.';
            }
        }

        return $this->render('frontend/user/messenger/dialog.html.twig', array(
            'dialog' => $dialog,
            'messages' => $this->getDoctrine()->getManager()->getRepository('AppBundle:InlineMessages')->findAll(),
            'history' => $dialog->getHistory(),
            'contact' => $this->getDoctrine()->getRepository('AppBundle:User')->find($contactId),
            'message' => $blacklist
        ));
    }

    /**
     * @Route("/messenger/message/delete/{message_id}/{contact_id}/userId:{userId}", name="user_messenger_message_ttt")
     */
    public  function postDeleteMessageAction($message_id, $contact_id, $userId) {
        $messenger = $this->get('messenger');

        if ($message_id && $userId !== null) {
            echo $messenger->deleteMessage($message_id, $userId, $contact_id);
        }else {

            echo $messenger->deleteMessageInbox($userId, $contact_id);
        }
    }

    /**
     * @Route("/messenger/activeChats/newMessages/userId:{userId}/contactId:{contactId}/{checkForDialogAlso}", defaults={"checkForDialogAlso" = false}, name="user_messenger_active_chats_new_messages")
     */
    public function activeChatsNewMessagesAction($userId, $contactId, $checkForDialogAlso)
    {
        $options['userId'] = $userId;
        $messenger = $this->get('messenger');
        //$result = $messenger->checkActiveChatsNewMessages($options);
        $result['newMessages'] = array();

        if (!count($result['newMessages'])) {
            if ($checkForDialogAlso) {
                $options['contactId'] = $contactId;
                $result = $messenger->checkDialogNewMessages($options);
            }
        }
        /*
        $post = $this->getRequest()->request->all();
        $result['readMessages'] = $messenger->checkMessagesIfRead($post['messages']);
        */
        return $messenger->response($result);

    }


    /**
     * @Route("/user/newMessages", name="user_get_new_messages")
     */
    public function getNewMessagesAction()
    {
        //get_statistics
        $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
        $delay = new \DateTime();
        $delay->setTimestamp(strtotime(
                $settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber() . ' minutes ago')
        );
        $em = $this->getDoctrine()->getManager();
        $conn = $em->getConnection();

        $res = $conn->query("CALL get_statistics ('"
            . $delay->format('Y-m-d H:i:s.000000') . "', '"
            . $this->getUser()->getId() . "', '"
            . $this->getUser()->getGender()->getId() . "')")
            ->fetch();
        
//        $conn = $this->getDoctrine()->getManager()->getConnection();
//        $res = $conn->query("CALL get_new_messages ('"
//            . $this->getUser()->getId() . "', '"
//            . $this->getUser()->getGender()->getId() . "')")
//            ->fetch();

        if ($res['newMessagesNumber'] > 0) {
            $res['messages'] = $conn->query("CALL get_new_messages_not_notified ('"
                . $this->getUser()->getId() . "')")
                ->fetchAll();
            $res['newMessagesText'] = $this->get('translator')->trans('!יש לך הודעה חדשה');
        } else {
            $res['messages'] = array();
        }


        /*$em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT u.name, COUNT(f.id)
            FROM AppBundle:Favorite f
            JOIN AppBundle:Favorite f
            WHERE f.isReceived = 0 AND f.member = '. $this->getUser()->getId()
        );

        $favorites = $query->getResult();*/
        if(count($res['messages']) == 0) {
            $RAW_QUERY = 'SELECT fl.id, fl.ext, fl.is_main, u.username, u.id as user_id, u.gender_id
            FROM favorite f
            LEFT JOIN user u
            ON u.id = f.owner_id
            LEFT JOIN file fl
            ON fl.user_id = f.owner_id
            WHERE f.is_received = 0 AND f.member_id = ' . $this->getUser()->getId() . ' LIMIT 1';
            $statement = $conn->prepare($RAW_QUERY);
            $statement->execute();

            $result = $statement->fetchAll();

            $RAW_QUERY = "UPDATE favorite SET is_received = '1' WHERE is_received = '0' AND member_id = " . $this->getUser()->getId();
            $statement = $conn->prepare($RAW_QUERY);
            $statement->execute();

            $res['favoritesCount'] = 0;

            if ($result) {
                $result = $result[0];
                $res['messages'][] = array(
                    'id' => 0,
                    'userId' => $result['user_id'],
                    'username' => $result['username'],
                    'test' => $result,
                    'mainPhoto' => '/media/photos/' . $result['user_id'] . '/' . $result['id'] . '.' . $result['ext'],
                    'noPhoto' => '/images/no_photo_' . $result['gender_id'] . '.png',
                    ///user/messenger/dialog/open/userId:{userId}/contactId:{contactId}", name="messenger_dialog_open
                    //'chatLink' => $this->generateUrl('messenger_dialog_open', array('userId' => $this->getUser()->getId(), 'contactId' => $result['user_id']))
                    'chatLink' => $this->generateUrl('view_user', array('id' => $result['user_id']))
                );
                $res['favoritesCount'] = 1;
                $res['newMessagesText'] = $this->get('translator')->trans('צירפ/ו אותך לרשימת המועדפים');
            }
        }

        return new Response(
            json_encode($res)
        );
    }


    /**
     * @Route("/messenger/newMessages/userId:{userId}", name="user_messenger_new_messages")
     */
    public function newMessagesAction($userId)
    {
        $messenger = $this->get('messenger');
        $options['userId'] = $userId;
        $options['lastLoginAt'] = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($userId)
            ->getLastloginAt()
            ->format('Y-m-d H:i:s');

        //$this->setUpCloudinary();
        return $messenger->checkNewMessages($options);
    }


    /**
     * @Route("/messenger/newMessagesMobile/{userId}/{contactId}", name="user_messenger_new_messages_mobile")
     */
    public function newMessagesMobileAction($userId, $contactId)
    {
        $messenger = $this->get('messenger');
        $options['userId'] = $userId;
        $options['contactId'] = $contactId;
        return $messenger->checkNewMessagesMobile($options);
    }


    /**
     * @Route("/messenger/message/send/userId:{userId}/contactId:{contactId}", name="user_messenger_message_send")
     * @Route("/messenger/message/send/userId:{userId}/contactId:{contactId}", name="user_messenger_message_send")
     */
    public function sendMessageAction(Request $request, $userId, $contactId)
    {
        $message = $request->request->get('message');
        $options['message'] = strip_tags($message);
        $options['message'] = urlencode($message);
        $options['userId'] = $userId;
        $options['contactId'] = $contactId;

        $chat = new Chat($options);

        $chatIsNotActive = $chat->getUsersBlockStatus();
        if ($chatIsNotActive != '0') {
            return $chat->response(array('success' => false, 'chatIsNotActive' => $chatIsNotActive));
        }

        if ($chat->isForbidden()) {
            return $chat->response(array('success' => false, 'chatIsForbidden' => true));
        }

        if ($chat->contact()->isFrozen()) {
            return $chat->response(array('success' => false, 'contactIsFrozen' => true));
        }

        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);

        if ($chat->isLimit($settings->getSendMessageUsersNumber())) {
            return $chat->response(array('success' => false, 'isLimit' => true));
        }

        if (!$chat->genderAllowed()) {

            return $chat->response(array('success' => false, 'gender' => 'gender not accepted'));
        }

        $em = $this->getDoctrine()->getManager();
        $owner = $em->getRepository('AppBundle:User')->find($options['userId']);
        $member = $em->getRepository('AppBundle:User')->find($options['contactId']);
        $viwed = $em->getRepository('AppBundle:Communication')->findOneBy(array('owner' => $owner, 'member' => $member));
        if (!$viwed) {

            $communication = new Communication();
            $communication->setOwner($owner);
            $communication->setMember($member);
            $em->persist($communication);
            $em->flush();
        }

        $messageObj = $chat->sendMessage();

        if ($messageObj) {

            //  if($chat->isNotSentToday()){

            $contact = $member; //$this->getDoctrine()->getRepository('AppBundle:User')->find($chat->contact()->getId());
            $user = $owner; //$this->getDoctrine()->getRepository('AppBundle:User')->find($chat->user()->getId());

            if($contact->getIsSentEmail() and $chat->isNotSentToday()) {

                $subject = 'זיגזוג' . ' | ' . 'הודעה חדשה';

                $body = '<div dir="rtl">';
                $body .=  'ממתינה לך הודעה חדשה בזיגזוג!'. '<br>';
                $body .= ' מ '  . '<strong>' . $user->getUsername() . '</strong><br>';
                $body .= 'גיל: ' . $user->age() . '<br>';
                if($user->getCity()) {
                    $body .= 'עיר: ' . $user->getCity()->getName() . '<br>';
                }

                $body .= '</div>';
                $body .= '<p dir="rtl">
                 בברכה,
                 <br>
                זיגזוג
                <br>
                zigzug.co.il
                 </p>';

                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:  admin@zigzug.co.il <admin@zigzug.co.il>' . "\r\n";

                mail($contact->getEmail(), $subject, $body, $headers);

            }
            if (($contact->getIsSentPush() and $chat->isNotSentToday()) or $contact->getId() == 23704 or $contact->getId() == 14806) {
                $messenger = $this->get('messenger');
                $messenger->pushNotification('קיבלת הודעה חדשה מ' . $user->getUsername(), $contact->getId(), $userId);
            }
            //  }

//            $contact = $this->getDoctrine()->getRepository('AppBundle:User')->find($chat->contact()->getId());
//            $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($chat->user()->getId());
//            $messenger = $this->get('messenger');
//            $messenger->pushNotification('You got a new message from ' . $user->getUsername(), $contact->getId());


            return $chat->response(array(
                'success' => true,
                'test1' => $contact->getEmail(),
                'message' => $messageObj
            ));
        }

        return $chat->response(array('success' => false));
    }


    /**
     * @Route("/messenger/checkMessagesIfRead/userId:{userId}", name="user_messenger_check_messages_if_read")
     */
    public function checkMessagesIfReadAction(Request $request, $userId)
    {
        $options['userId'] = $userId;
        $messenger = $this->get('messenger');
        $post = $request->request->all();
        $result['readMessages'] = $messenger->checkMessagesIfRead($post['messages']);
        return $messenger->response($result);
    }


    /**
     * @Route("/messenger/message/read/messageId:{messageId}/userId:{userId}/contactId:{contactId}", name="user_messenger_message_read")
     */
    public function readMessageAction($messageId, $userId, $contactId)
    {
        $options['userId'] = $userId;
        $options['contactId'] = $contactId;

        $chat = new Chat($options);
        $result = $chat->setMessageAsRead($messageId);
        return $chat->response(array('success' => $result));
    }


    /**
     * @Route("/messenger/message/notify/messageId:{messageId}/userId:{userId}", name="user_messenger_message_notify")
     */
    public function notifyMessageAction($messageId, $userId)
    {
        $messenger = $this->get('messenger');
        $result = $messenger->setMessageAsNotified($messageId);
        return $messenger->response(array('success' => $result));
    }


    /**
     * @Route("/messenger/message/messageId:{messageId}/userId:{userId}/useFreePointToRead", name="user_use_free_point_to_read")
     */
    public function useFreePointToReadMessageAction($messageId, $userId)
    {
        $messenger = $this->get('messenger');
        return $messenger->useFreePointToReadMessage($messageId, $userId);
    }


    public function setUpCloudinary()
    {
        /*
        \Cloudinary::config(array(
            "cloud_name" => "greendate",
            "api_key" => "333193447586872",
            "api_secret" => "rT6Kccy2ZHThaBlFzlOeLKE085o"
        ));
        */
    }


}
