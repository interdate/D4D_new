<?php


namespace App\Services\Messenger;


use App\Entity\LocCities;
use App\Entity\LocCountries;
use App\Entity\Lookingfor;
//use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class Messenger
{
    public $db;
    public $config;
    public $isNewMessage = false;
    public $em = false;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->config = Config::getInstance();
        $this->config = $this->arrayToObject($this->config);
        $this->db = Database::getInstance($this->config->database);
        //$this->db = $db;
        date_default_timezone_set('America/New_York');
        $this->em = $entityManager ? $entityManager : false;
    }

    public function response($array)
    {
        return new JsonResponse($array);
    }

    public static function arrayToObject($array)
    {
        $json = json_encode($array);
        return json_decode($json);
    }

    public function isNewMessage()
    {
        return $this->isNewMessage;
    }

    public function openChat($options)
    {
        $userAttributes = new UserAttributes();

        $chatSession = $userAttributes->get($this->config->messengerSession, array($options['userId'], $options['contactId']));
        if (count($chatSession) == 0) {
            $userAttributes->post($this->config->messengerSession, array($options['userId'], $options['contactId']));
            return true;
        }

        return false;
    }

    public function closeChat($options)
    {
        $sql = "DELETE FROM " . $this->config->messengerSession->table . " WHERE userId = ? AND contactId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $options['userId'], \PDO::PARAM_INT);
        $stmt->bindParam(2, $options['contactId'], \PDO::PARAM_INT);
        $success = ($stmt->execute()) ? true : false;
        return $this->response(array('success' => $success));
    }

    public function getActiveChats($options)
    {
        $userAttributes = new UserAttributes();
        $activeChats = array();

        $sql = "
			SELECT
				s.userId, s.contactId, u.username FROM " . $this->config->messengerSession->table . " s
			JOIN
				" . $this->config->users->table . " u
			ON
				(s.contactId = u.id)
			WHERE
				s.userId = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $options['userId'], \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll();

        foreach ($result as $row) {

            $chat = new Chat(array(
                'userId' => $row['userId'],
                'contactId' => $row['contactId']
            ));

            if (!$chat->isForbidden()) {
                $activeChats[] = array(
                    'id' => $row['contactId'],
                    'name' => $row['userNic']
                );
            }
        }

        return $activeChats;
    }

    public function checkActiveChatsNewMessages($options)
    {
        $result = array();
        $dateTime = array();
        $userAttributes = new UserAttributes();
        if (!isset($options['contactId'])) {
            $allChats = $userAttributes->get($this->config->messengerSession, array($options['userId']));
        } else {
            $allChats = array($options);
        }
        if (count($allChats)) {

            $startTime = time();
            while (time() - $startTime < 10) {

                foreach ($allChats as $chatOptions) {
                    $chat = new Chat($chatOptions);
                    $newMessages = $chat->getNewMessages();
                    //var_dump($newMessages);
                    if (count($newMessages) > 0) {
                        $allowedToReadMessage = ($chat->user()->isPaying() || $chat->contact()->isPaying()) ? true : false;
                        foreach ($newMessages as $message) {
                            $message = (array)$message;
                            $this->isNewMessage = true;
                            $messageDateObject = new \DateTime($message['date']);
                            $timestamp = $messageDateObject->getTimestamp();
                            $date = date("m/d/Y", $timestamp);
                            $time = date("H:i", $timestamp);

                            $text = ($message['fromUser'] != $chat->user()->getId() && !$allowedToReadMessage)
                                ? ''
                                : nl2br(urldecode($message['message']));

                            $result[] = array(
                                "id" => $message['messageId'],
                                "from" => $chat->contact()->getId(),
                                "text" => $text,
                                "dateTime" => $date . ' ' . $time,
                                "userImage" => $chat->contact()->getImage(),
                                "userName" => $chat->contact()->getNickName(),
                                "allowedToRead" => $allowedToReadMessage
                            );
                        }
                    }
                }

                if ($this->isNewMessage()) {
                    $timestamp = time();
                    $time = date("i:s", $timestamp);
                    $dateTime[] = $time;

                    foreach ($result as $message) {
                        $chat->setMessageAsDelivered($message['id']);
                    }

                    //return $this->response(array("newMessages" => $result, "MinSec" => $dateTime));
                    //exit(0);
                    return array(
                        "newMessages" => $result,
                        "currentUserHasPoints" => $chat->user()->hasPoints(),
                        "MinSec" => $dateTime,
                    );

                }

                usleep(500);
            }

        }

        $timestamp = time();
        $time = date("i:s", $timestamp);
        $dateTime[] = $time;
        //return $this->response(array("newMessages" => $result, "MinSec" => $dateTime));
        return array("newMessages" => $result, "MinSec" => $dateTime);
    }

    public function checkMessagesIfRead($messages)
    {
        /*
        ini_set('display_errors',1);
        ini_set('display_startup_errors',1);
        error_reporting(-1);
        */

        $readMessages = array();
        $startTime = time();
        while (time() - $startTime < 10) {
            if (mb_strlen(trim($messages), "utf-8") > 0) {
                $sql = "SELECT messageId FROM " . $this->config->messenger->table . " WHERE messageId IN (" . trim($messages) . ") AND isRead = 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                foreach ($stmt->fetchAll() as $row) {
                    $readMessages[] = $row['messageId'];
                }

                if (count($readMessages)) {
                    return $readMessages;
                }
            }

            usleep(500);
        }

        return $readMessages;
    }


    public function checkNewMessages($options)
    {

        $users = array();

        $sql = "
			SELECT 
				m.fromUser, m.message, m.isRead, m.isDelivered, u.username FROM " . $this->config->messenger->table . " m
			JOIN 					 
				" . $this->config->users->table . " u 
			ON
				( m.fromUser = u.id)					
			WHERE 
				m.toUser = ? AND m.isDelivered = 0 AND m.isRead = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $options['userId'], \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll();

        foreach ($result as $row) {

            $user = array(
                "id" => $row['fromUser'],
                "name" => $row['username'],
                "isDelivered" => $row['isDelivered'],
                "isRead" => $row['isRead'],
                //"text"=> urldecode($row['message'])
            );

            if (!in_array($user, $users)) {
                $users[] = $user;
            }
        }

        return $this->response(array("fromUsers" => $users));
    }

    public function checkDialogNewMessages($options)
    {
        $result = array();
        $dateTime = array();
        $startTime = time();

        while (time() - $startTime < 10) {

            $dialog = new Dialog($this->em, $options);
            $newMessages = $dialog->getNewMessages();

            //return $this->response(array("newMessages" => $newMessages, "MinSec" => $dateTime));
            //die();

            if (count($newMessages) > 0) {
                $allowedToReadMessage = ($dialog->user()->isPaying() || $dialog->contact()->isPaying()) ? true : false;

                foreach ($newMessages as $message) {
                    $this->isNewMessage = true;
                    $messageDateObject = new \DateTime($message['date']);
                    $timestamp = $messageDateObject->getTimestamp();
                    $date = date("m/d/Y", $timestamp);
                    $time = date("H:i", $timestamp);
                    $text = ($message['fromUser'] != $dialog->user()->getId() && !$allowedToReadMessage)
                        ? ''
                        : nl2br(urldecode($message['message']));

                    $result[] = array(
                        "id" => $message['messageId'],
                        "from" => $dialog->contact()->getId(),
                        "text" => $text,
                        "dateTime" => $date . ' ' . $time,
                        "userImage" => $dialog->contact()->getImage(),
                        "userName" => $dialog->contact()->getNickName(),
                        "allowedToRead" => $allowedToReadMessage,
                    );
                }
            }

            if ($this->isNewMessage()) {
                $timestamp = time();
                $time = date("i:s", $timestamp);
                $dateTime[] = $time;

                foreach ($result as $message) {
                    $dialog->setMessageAsDelivered($message['id']);
                }

                //return $this->response(array("newMessages" => $result, "MinSec" => $dateTime));
                //exit(0);
                return array(
                    "newMessages" => $result,
                    "currentUserHasPoints" => $dialog->user()->hasPoints(),
                    "MinSec" => $dateTime,
                );
            }

            usleep(500);
        }

        $timestamp = time();
        $time = date("i:s", $timestamp);
        $dateTime[] = $time;
        //return $this->response(array("newMessages" => $result, "MinSec" => $dateTime));
        return array("newMessages" => $result, "MinSec" => $dateTime);
    }

    public function getNewMessagesNumber($options = false)
    {

        $sql = "
			SELECT
				m.messageId FROM " . $this->config->messenger->table . " m	
			JOIN
				user u
			ON
				u.id = m.fromUser 
				AND u.is_blocked = 0 
				AND u.is_frozen = 0 
				AND u.is_not_activated = 0
			WHERE
				m.toUser = ? AND m.isRead = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $options['userId'], \PDO::PARAM_INT);
        $stmt->execute();
        return count($stmt->fetchAll());
    }


    public function getAllUsersMessages($page, $perPage, $userId = 'null')
    {
        /*
        $messages = array();

        $sql = "EXEC admin_massages_sa " . $page . "," . $perPage . "," . $userId;
        $stmt = $this->db->query($sql);
        $result = $stmt->fetchAll();

        $messages['itemsNumber'] = $result[0][0];
        $stmt->nextRowset();
        //$result = $stmt->fetchAll();
        $messages['items'] = $stmt->fetchAll();

        $i = 0;

        */
        ini_set('user_agent', $_SERVER['HTTP_USER_AGENT']);
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "authCode: interdate\r\n"
            )
        );
        $context = stream_context_create($opts);
        $json = file_get_contents('https://m.dating4disabled.com/api/v7/admin/userMessages/' . $page . '/' . $perPage . '/' . $userId, false, $context);
        $messages = json_decode($json, true);
        //var_dump($messages);
        foreach ($messages['items'] as $i => $item) {
            $item['message'] = nl2br(urldecode($item['message']));
            $messages['items'][$i] = $item;

        }
        return $messages;

    }

    public function useFreePointToReadMessage($messageId, $userId, $api = false)
    {
        $user = new User($userId);
        if ($user->hasPoints()) {
            $sql = "SELECT fromUser, message FROM " . $this->config->messenger->table . " WHERE messageId = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $messageId, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();

            $result = array(
                'success' => true,
                'message' => array(
                    'from' => $row['fromUser'],
                    'text' => nl2br(urldecode($row['message'])),
                )
            );

            $sql = "UPDATE " . $this->config->messenger->table . " SET isRead = 1, isDelivered = 1 WHERE messageId = '" . $messageId . "'";
            $stmt = $this->db->query($sql);

            $sql = "UPDATE user SET points = points - 1 WHERE id = '" . $userId . "'";
            $stmt = $this->db->query($sql);
            if ($api) {
                return $result['message']['text'];
            }
            return $this->response($result);
        }

        return $this->response(array('success' => false));
    }

    public function setMessagesAsRead($unreadMessagesString)
    {
        $sql = "UPDATE messenger SET isRead = 1, isDelivered = 1 WHERE messageId IN (" . $unreadMessagesString . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $unreadMessagesString;
    }

    public function getNewMessCount($userId)
    {
        $sql = "
    		SELECT 
    			messageId 
    		FROM 
    			messenger m
    		JOIN
    			user u
    		ON
    			u.id = m.fromUser 
    			AND u.is_blocked = 0 
    			AND u.is_not_activated = 0 
    			AND u.is_frozen = 0
    		WHERE 
    			m.toUser = :userId 
    			AND m.isRead = 0 
    			AND m.fromUser != 0
				AND m.fromUser != m.toUser";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['userId' => $userId]);
        return count($stmt->fetchAll());
    }

    public function getNotDeliveredMessage($userId)
    {
        $sql = "
			SELECT
    			m.messageId as id,
				u.id as userId,
				u.username as username,
				'***New Message***' as newMessagesText,
				CASE 
				    WHEN i.id > 0 THEN CONCAT('/media/photos/', u.id, '/', i.id, '.', i.ext)
				    ELSE 
						CASE 
							WHEN u.gender_id = 2 THEN '/images/no_photo_2.jpeg'
							WHEN u.gender_id = 1 THEN '/images/no_photo_1.jpeg'
							ELSE 
							'/images/no_photo_1.jpeg'
						END
				END AS mainPhoto
    		FROM
    			messenger m
    		JOIN
    			user u
    		ON
    			u.id = m.fromUser
    			AND u.is_blocked = 0
    			AND u.is_not_activated = 0
    			AND u.is_frozen = 0
			LEFT JOIN 
				images i
			ON 
				i.user_id=u.id 
				AND i.main = 1 
				AND i.validated = 1
    		WHERE
    			m.toUser = :userId
    			AND m.isRead = 0
    			AND m.fromUser != 0 
				AND m.isDelivered = 0";
        $stmt = $this->db->prepare($sql);
        //$stmt->bindParam(1, $this->userId, PDO::PARAM_INT);
        $stmt->execute(['userId' => $userId]);
        $res = $stmt->fetch();
        return $res;
    }

    public function removeMessages($messagesIdsString)
    {
        $sql = "DELETE FROM " . $this->config->messenger->table . " WHERE messageId IN (" . $messagesIdsString . ")";
        $stmt = $this->db->query($sql);
    }

    public function loadClasses()
    {
        require $_SERVER['DOCUMENT_ROOT'] . '/../src/Services/Messenger/DeviceEntity.php';
        //require $_SERVER['DOCUMENT_ROOT'] . '/../src/Services/Messenger/DeviceEntityHandler.php';
        require $_SERVER['DOCUMENT_ROOT'] . '/../src/Services/Messenger/iOSDevice.php';
        require $_SERVER['DOCUMENT_ROOT'] . '/../src/Services/Messenger/AndroidDevice.php';
    }

    public function getDeviceInstance($OS)
    {
        switch ($OS) {
            case 'iOS' :
                return new iOSDevice();
                break;

            case 'Android' :
                return new AndroidDevice();
                break;

            default:

                break;
        }
    }

    public function pushNotification($message, $userId, $id = 0)
    {
        $sql = "SELECT * FROM userdevices WHERE user_id = :userId";
        $stmt = $this->db->prepare($sql);
        // $stmt->bindParam(1, $userId, \PDO::PARAM_INT);
        $stmt->execute(['userId' => $userId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
//            if(!empty($row['gsmdeviceid'])){
//
//            }
            $os = (!empty($row['gsmdeviceid'])) ? 'Android' : 'iOS';
            $id = (!empty($row['gsmdeviceid'])) ? $row['gsmdeviceid'] : $row['apndeviceid'];
            $devClass = $this->getDeviceInstance($os);
            $devClass->setProps(array('userId' => $userId, 'id' => $id));
            if ($os == 'Android') $devClass->pushNotification($message);
        }
    }

    public function getUsersMessages($page, $perPage, $userId, $conn)
    {
        $offset = ($page - 1) * $perPage;
        $userCond = $userId === null ? '' : 'WHERE m.fromUser = ' . $userId . ' OR m.toUser = ' . $userId;


        $sql = "
            SELECT
              m.messageId,
              m.fromUser,
              m.toUser,
              m.message,
              m.date,
              fromUser.username as fromUsername,
              toUser.username as toUsername
            FROM
              messenger m
            LEFT JOIN
              user fromUser
            ON fromUser.id = m.fromUser

            LEFT JOIN
              user toUser
            ON toUser.id = m.toUser
            " . $userCond . "
            ORDER BY m.date DESC
            LIMIT " . $offset . "," . $perPage;

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $messages = $stmt->fetchAll();

        foreach ($messages as $key => $item) {
            $messages[$key]['message'] = nl2br(urldecode($item['message']));
        }

        return $messages;

    }

    public function getUsersMessagesNumber($userId)
    {
        $userCond = $userId === null ? '' : 'WHERE m.fromUser = ' . $userId . ' OR m.toUser = ' . $userId;

        $sql = "
            SELECT
              COUNT(m.messageId) as number
            FROM
              messenger m
            LEFT JOIN
              user fromUser
            ON fromUser.id = m.fromUser

            LEFT JOIN
              user toUser
            ON toUser.id = m.toUser
            " . $userCond;

        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['number'];

    }

    public function getPushToken(&$data, $type)
    {
        $field = ($type == 'android') ? 'gcmdeviceid' : (($type == 'ios') ? 'apndeviceid' : $type);
        $sql = 'SELECT ' . $field . ' AS token FROM `userdevices` ud 
                JOIN user u ON (u.id = ud.user_id)
                WHERE ud.user_id IN (' . $data['users'] . ') AND ud.' . $field . ' IS NOT NULL AND u.is_push_on_new_mess = 1';
        $stmt = $this->db->prepare($sql);
//        var_dump($sql);die;
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $res = $stmt->fetchAll();
        $data[$type] = $res;
    }

    public function adminAndroidPush($data, $page = 0)
    {

        $max_per_1 = 500;
        $need_more = false;

        $tokens = [];


        $count = count($data['android']);

        $now = $page * $max_per_1;

        if ($count - $now > $max_per_1) {
            $need_more = true;
        }

        for ($i = $now; $i < $now + $max_per_1; $i++) {

            if (!isset($data['android'][$i])) {
                $need_more = false;
                break;
            }

            $tokens[] = $data['android'][$i]['token'];

        }

//
//dump($data);die;
        $pushData = array(
            'registration_ids' => $tokens,
            'data' => array(
                'title' => $data['titleMess'],
                'message' => $data['message'],
                'count' => 1,
                'color' => '#e20020',
                'content-available' => '0',
                'userFrom' => $data['user_id'],
                'image' => $data['image'],
                'vibrate' => 1,
                'notId' => time(),
                "titleMess" => $data['titleMess'] ?? 'You got a new message',
                "onlyInBackgroundMode" => $data['onlyInBackgroundMode'] ?? true,
                'userId' => $data['user_id'],
                'url' => $data['url'] ?? '/dialog',
                'type' => $data['type'] ?? 'linkIn',
                'priority' => 'height',
            ),
        );

        $headers = array(
            'Authorization: key=' . 'AIzaSyBDYZL3DA6M38saU3jOgam0l6RmB3o_cSM',
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pushData));

        $result = curl_exec($ch);

//        if ($result === FALSE) {
//            die('Curl failed: ' . curl_error($ch));
//        }

        curl_close($ch);

//        dump($result);die;
        if ($need_more) {
            $this->adminAndroidPush($data, ++$page);
        }


    }


    public function adminWebPush($data, $page = 0)
    {

        $max_per_1 = 500;
        $need_more = false;

        $count = count($data['browser']);

        $now = $page * $max_per_1;

        if ($count - $now > $max_per_1) {
            $need_more = true;
        }


        if (isset($data['browser']) && is_array($data['browser'])) {
            $fields = array(
                'notification' => array(
                    'body' => $data['message'],
                    'title' => $data['titleMess'],
                    'icon' => '/images/icon.png',
                    'click_action' => htmlspecialchars($data['webUrl'], ENT_COMPAT),
                ),
                'priority' => 'high',
            );


            $headers = array(
                'Authorization: key=' . 'AIzaSyBDYZL3DA6M38saU3jOgam0l6RmB3o_cSM',
                'Content-Type: application/json'
            );

            $tokens = [];

            for ($i = $now; $i < $now + $max_per_1; $i++) {
                if (!isset($data['browser'][$i])) {
                    $need_more = false;
                    break;
                }
                $tokens[] = $data['browser'][$i]['token'];
            }

            $fields['registration_ids'] = $tokens;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

            $result = curl_exec($ch);

//                if ($result === FALSE) {
//                    die('Curl failed: ' . curl_error($ch));
//                }

            curl_close($ch);
//var_dump($result);
            if ($need_more) {
                $this->adminWebPush($data, ++$page);
            }

        }
    }

    public function validateData($user, $type = 'register')
    {
        $userAttributes = new UserAttributes();
        $err = array();

        if (isset($user->step)) {
            if ($user->step == 1) {
                //validate step 1
                if (strlen($user->userEmail) < 3 or preg_match('/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i', $user->userEmail) == 0) {
                    if (strlen($user->userEmail) < 3) {
                        $err['userEmail'] = 'Email error';
                    } else {
                        $err['userEmail'] = 'The Email "' . $user->userEmail . '" is not a valid';
                    }
                }

                $usersRepo = $this->em->getRepository('User');
                if (!isset($err['userEmail'])) {
                    $user->userEmail = trim($user->userEmail);
                    $existingUser = $usersRepo->findOneByUseremail($user->userEmail);
                    if (isset($user->userId) and is_object($existingUser) and $user->userId == $existingUser->getUserid()) {
                        $existingUser = false;
                    }
                    if (!$existingUser) {
                        //block Email exists
                        $sql = "
							SELECT
								userEmail
							FROM
								globalBlackList
							WHERE
								userEmail LIKE '%" . $user->userEmail . "%'";


                        $stmt = $this->db->prepare($sql);
                        $stmt->execute();
                        $result = $stmt->fetch();
                        if ($result) {
                            $existingUser = true;
                        }
                    }
                    if ($existingUser and is_object($existingUser) and (!isset($user->userId) or (isset($user->userId) and $user->userId != $existingUser->getUserid()))) {
                        $err['userEmail'] = 'The Email ' . $user->userEmail . ' already exists';
                    }
                }

                $user->userNick = trim($user->userNick);
                $user->userNick = preg_replace('/[^A-Za-z0-9 _\-\+,\.:]/', "", $user->userNick);
                if (strlen($user->userNick) < 3) {
                    $err['userNick'] = 'The Username error';
                }
                $existingUser = false;
                $existingUser = $usersRepo->findOneByUsernic($user->userNick);
                if ((!isset($user->userId) and is_object($existingUser)) or (isset($user->userId) and is_object($existingUser) and $existingUser->getUserid() != $user->userId)) {
                    $err['userNick'] = 'The Username ' . $user->userNick . ' already exists';
                }

                //password
                if ($type == 'register') {
                    if (strlen($user->userPass) < 4 or strlen($user->userPass) > 12) {
                        $err['userPass'] = "Invalid password (must be 4 to 12 characters)";
                    }
                    if ($user->userPass != $user->userPass2) {
                        $err['userPass2'] = "Error in retyped password";
                    }
                    if (isset($user->agree) and $user->agree == 0) {
                        $err['agree'] = 'You need to confirm the Terms and Conditions';
                    }
                }

                if ($user->userGender == '') {
                    $err['userGender'] = 'Please choose gender';
                }


                if (empty($user->userBirthday->y) or empty($user->userBirthday->m) or empty($user->userBirthday->d)) {
                    $err['userBirthday'] = 'Date Of Birth error';
                } else {

                    $userBirthdayObj = date_create_from_format('Y-n-j', $user->userBirthday->y . "-" . $user->userBirthday->m . "-" . $user->userBirthday->d);
                    //var_dump($userBirthdayObj->format('Y-m-d'));die();
                    if (date_diff(date_create($userBirthdayObj->format('Y-m-d')), date_create('today'))->y < 18) {
                        $err['userBirthday'] = 'Your age is not under 18 years old';//'Date Of Birth error'
                    }
                }
                //$user->userBirthday['y'] . "-" . $user->userBirthday['m']  . "-". $user->userBirthday['d'] ." 00:00:00"

                if ($user->countryCode == '--') {
                    $err['countryCode'] = 'Country error';
                }

                if ($user->regionCode == '--' or !$this->isValidRegionCode($user)) {
                    $err['regionCode'] = 'Region error';
                    if ($user->countryCode == 'US') {
                        $err['regionCode'] = 'State error';
                    }
                }

                if ($user->countryOfOriginCode == '--') {
                    $err['countryOfOriginCode'] = 'Country of Origin error';
                }

                if ($user->sexPrefId == '--') {
                    $err['sexPrefId'] = 'Sexual Preference error';
                }

                if (strlen($user->userCityName) < 3) {
                    $err['userCityName'] = 'City error';
                }

                if ($user->countryCode == 'US' and (strlen($user->zipCode) < 3 or strlen($user->zipCode) > 5)) {
                    $err['zipCode'] = 'Zip Code error';
                }
            }
            if ($user->step == 2) {

                if (strlen($user->userfName) < 2) {
                    $err['userfName'] = 'First Name error';
                }

                if (strlen($user->userlName) < 2) {
                    $err['userlName'] = 'Last Name error';
                }

                if ($user->maritalStatusId == '--') {
                    $err['maritalStatusId'] = 'Marital Status error';
                }

                if ($user->userChildren == '--') {
                    $err['userChildren'] = 'Children error';
                }

                if ($user->ethnicOriginId == '--') {
                    $err['ethnicOriginId'] = 'Ethnicity error';
                }

                if ($user->religionId == '--') {
                    $err['religionId'] = 'Religion error';
                }

                if ($user->educationId == '--') {
                    $err['educationId'] = 'Education error';
                }

                if ($user->occupationId == '--') {
                    $err['occupationId'] = 'Occupation error';
                }

                if ($user->incomeId == '--') {
                    $err['incomeId'] = 'Income error';
                }

                if (count((array)$user->languageId) == 0) {
                    $err['languageId'] = 'Language error';
                }

                if ($user->bodyTypeId == '--') {
                    $err['bodyTypeId'] = 'Body Style error';
                }

                if ($user->userHight == '--') {
                    $err['userHight'] = 'Height error';
                }

                if ($user->hairLengthId == '--') {
                    $err['hairLengthId'] = 'Hair style error';
                }

                if ($user->hairColorId == '--') {
                    $err['hairColorId'] = 'Hair color error';
                }

                if ($user->eyesColorId == '--') {
                    $err['eyesColorId'] = 'Eyes color error';
                }

                if ($user->smokingId == '--') {
                    $err['smokingId'] = 'Smoking error';
                }

                if ($user->drinkingId == '--') {
                    $err['drinkingId'] = 'Drinking error';
                }


                $tempUser = new StdClass;
                $tempUser->aboutMe = $user->userAboutMe;
                $tempUser->lookingFor = $user->userLookingFor;
                $tempUser = $this->clearUserDataSpam($tempUser);
                $user->userAboutMe = $tempUser->aboutMe;
                $user->userLookingFor = $tempUser->lookingFor;

                if (strlen($user->userAboutMe) < 10) {
                    $err['userAboutMe'] = 'About Me error(must be at least 10 characters)';
                }
                if (strlen($user->userLookingFor) < 10) {
                    $err['userLookingFor'] = 'Looking For error(must be at least 10 characters)';
                }

                if ($user->healthId == '--') {
                    $err['healthId'] = 'Life Challenge error';
                }

                if ($user->mobilityId == '--') {
                    $err['mobilityId'] = 'Mobility error';
                }

            }
        }
        return $err;
    }

    public function getForm($type, $user)
    {

        if (!isset($user->step)) {
            $user = new \StdClass;
            $user->step = 1;
        }

        $form = array();
        $userAttributes = new UserAttributes();
        $form['step'] = array(
            'name' => 'step',
            'type' => 'hidden',
            'label' => '',
            'value' => $user->step
        );

        if ($user->step == 1) {

            $form['userEmail'] = array(
                'name' => 'userEmail',
                'type' => 'text',
                'label' => '* Email',
                'value' => ((isset($user->userEmail)) ? $user->userEmail : ''),
                'required' => true,
            );

            $form['userNick'] = array(
                'name' => 'userNick',
                'type' => 'text',
                'label' => '* Username',
                'value' => ((isset($user->userNick)) ? $user->userNick : ''),
                'required' => true,
            );

            if ($type == 'register') {
                $form['userPass'] = array(
                    'name' => 'userPass',
                    'type' => 'password',
                    'label' => '* Password',
                    'value' => ((isset($user->userPass)) ? $user->userPass : ''),
                    'required' => true,
                );
                $form['userPass2'] = array(
                    'name' => 'userPass2',
                    'type' => 'password',
                    'label' => '* Retype Password',
                    'value' => ((isset($user->userPass2)) ? $user->userPass2 : ''),
                    'required' => true,
                );
            }
            $val = (isset($user->userNick)) ? $user->userGender : '';
            $valLabel = 'Choose';
            if ($val == '1') {
                $valLabel = 'Male';
            } elseif ($val == '2') {
                $valLabel = 'Female';
            } elseif ($val == '3') {
                $valLabel = 'Other';
            }
            $form['userGender'] = array(
                'name' => 'userGender',
                'type' => 'select',
                'label' => '* Gender',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => array(
                    array('value' => '', 'label' => 'Choose'),
                    array('value' => '1', 'label' => 'Male'),
                    array('value' => '2', 'label' => 'Female'),
                    array('value' => '3', 'label' => 'Other')
                ),
                'required' => true,
            );

            if ($type == 'register') {
                $form['agree'] = array(
                    'name' => 'agree',
                    'type' => 'checkbox',
                    'label' => '* I confirm that I have read and agreed to the Terms and Conditions of Service of membership at Dating4disabled.com.',
                    'value' => ((isset($user->agree)) ? $user->agree : 0),
                    'required' => true,
                );
            }

            $form['userCityName'] = array(
                'name' => 'userCityName',
                'type' => 'text',
                'label' => '* City',
                'value' => ((isset($user->userCityName)) ? $user->userCityName : ''),
                'required' => true,
            );

            $sexPrefChoices = $userAttributes->get($this->config->sexPreference, false, 'id as value, name as label');
            array_unshift($sexPrefChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->sexPrefId)) ? $user->sexPrefId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->sexPreference, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['sexPrefId'] = array(
                'name' => 'sexPrefId',
                'type' => 'select',
                'label' => '* Sexual Preference',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $sexPrefChoices,
                'required' => true,
            );

            $form['userBirthday'] = array(
                'name' => 'userBirthday',
                'type' => 'date',
                'label' => '* Date Of Birth',
                'value' => array('y' => ((isset($user->userBirthday) and !empty($user->userBirthday->y)) ? $user->userBirthday->y : ''), 'm' => ((isset($user->userBirthday) and !empty($user->userBirthday->m)) ? $user->userBirthday->m : ''), 'd' => ((isset($user->userBirthday) and !empty($user->userBirthday->d)) ? $user->userBirthday->d : '')),
                'required' => true,
            );

            $topCountries = $userAttributes->get($this->config->countries, array(array('US', 'GB', 'CA')), 'code as value, name as label', array('DESC'));
            $countriesChoices = $userAttributes->get($this->config->countries, array(array('!', '--', 'US', 'GB', 'CA')), 'code as value, name as label', array('', 'ASC'));
            array_unshift($countriesChoices, array('value' => '--', 'label' => 'Choose'), $topCountries[0], $topCountries[1], $topCountries[2]);
            $val = ((isset($user->countryCode)) ? $user->countryCode : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->countries, array($val), 'code as value, name as label, id');
                $valLabel = $valChoose['label'];
                $countryId = $valChoose['id'];
            }
            $form['countryCode'] = array(
                'name' => 'countryCode',
                'type' => 'select',
                'label' => '* Country',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $countriesChoices,
                'required' => true,
            );

            $val = ((isset($user->countryOfOriginCode)) ? $user->countryOfOriginCode : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->countries, array($val), 'code as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['countryOfOriginCode'] = array(
                'name' => 'countryOfOriginCode',
                'type' => 'select',
                'label' => '* Country of Origin',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $countriesChoices,
                'required' => true,
            );

            $defaultVal = false;
            $regionCodeChoices = array();
            if (isset($user->countryCode) and $user->countryCode != '--') {
                $regionCodeChoices = $userAttributes->get($this->config->regions_, array('', $countryId), 'code as value, name as label');
                if (isset($regionCodeChoices['label'])) {
                    $regionCodeChoices = array($regionCodeChoices);
                }
                foreach ($regionCodeChoices as $i => $regionCodeChoice) {
                    if ($regionCodeChoice['label'] == '--') {
                        $regionCodeChoices[$i]['label'] = 'Choose';
                        $defaultVal = true;
                    }
                }
                if ($user->countryCode == 'US') {
                    $form['zipCode'] = array(
                        'name' => 'zipCode',
                        'type' => 'text',
                        'label' => '* Zip Code',
                        'value' => ((isset($user->zipCode)) ? $user->zipCode : ''),
                        'required' => true,
                    );
                }
            }
            if (!$defaultVal) {
                array_unshift($regionCodeChoices, array('value' => '--', 'label' => 'Choose'));
            }
            $val = ((isset($user->regionCode)) ? $user->regionCode : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->regions_, array('', $countryId, $val), 'code as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['regionCode'] = array(
                'name' => 'regionCode',
                'type' => 'select',
                'label' => '* ' . ((isset($user->countryCode) and $user->countryCode == 'US') ? 'State' : 'Region'),
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $regionCodeChoices,
                'required' => true,
            );

        }
        if ($user->step == 2) {
            $form['userfName'] = array(
                'name' => 'userfName',
                'type' => 'text',
                'label' => '* First Name',
                'value' => ((isset($user->userfName)) ? $user->userfName : ''),
                'required' => true,
            );

            $form['userlName'] = array(
                'name' => 'userlName',
                'type' => 'text',
                'label' => '* Last Name',
                'value' => ((isset($user->userlName)) ? $user->userlName : ''),
                'required' => true,
            );

            $maritalStatChoices = $userAttributes->get($this->config->maritalStatus, false, 'id as value, name as label');
            array_unshift($maritalStatChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->maritalStatusId)) ? $user->maritalStatusId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->maritalStatus, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['maritalStatusId'] = array(
                'name' => 'maritalStatusId',
                'type' => 'select',
                'label' => '* Marital Status',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $maritalStatChoices,
                'required' => true,
            );

            $userChildrenChoices = array(array('value' => '--', 'label' => 'Choose'), array('value' => '0', 'label' => 'None'));
            for ($i = 1; $i < 11; $i++) {
                $userChildrenChoices[] = array(
                    'value' => $i, 'label' => $i
                );
            }
            $userChildrenChoices[] = array(
                'value' => 11, 'label' => '> 10'
            );
            $form['userChildren'] = array(
                'name' => 'userChildren',
                'type' => 'select',
                'label' => '* Children',
                'value' => ((isset($user->userChildren) and $user->userChildren != 'null') ? $user->userChildren : '--'),
                'valLabel' => ((isset($user->userChildren) and $user->userChildren != 'null') ? $user->userChildren : 'Choose'),
                'choices' => $userChildrenChoices,
                'required' => true,
            );

            $ethnicOriginChoices = $userAttributes->get($this->config->ethnicity, false, 'id as value, name as label');
            array_unshift($ethnicOriginChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->ethnicOriginId)) ? $user->ethnicOriginId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->ethnicity, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['ethnicOriginId'] = array(
                'name' => 'ethnicOriginId',
                'type' => 'select',
                'label' => '* Ethnicity',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $ethnicOriginChoices,
                'required' => true,
            );

            $religionChoices = $userAttributes->get($this->config->religions, false, 'id as value, name as label');
            array_unshift($religionChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->religionId)) ? $user->religionId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->religions, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['religionId'] = array(
                'name' => 'religionId',
                'type' => 'select',
                'label' => '* Religion',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $religionChoices,
                'required' => true,
            );

            $educationChoices = $userAttributes->get($this->config->education, false, 'id as value, name as label');
            array_unshift($educationChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->educationId)) ? $user->educationId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->education, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['educationId'] = array(
                'name' => 'educationId',
                'type' => 'select',
                'label' => '* Education',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $educationChoices,
                'required' => true,
            );

            $occupationChoices = $userAttributes->get($this->config->occupation, false, 'id as value, name as label');
            array_unshift($occupationChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->occupationId)) ? $user->occupationId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->occupation, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['occupationId'] = array(
                'name' => 'occupationId',
                'type' => 'select',
                'label' => '* Occupation',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $occupationChoices,
                'required' => true,
            );

            $incomeChoices = $userAttributes->get($this->config->income, false, 'id as value, name as label');
            array_unshift($incomeChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->incomeId)) ? $user->incomeId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->income, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['incomeId'] = array(
                'name' => 'incomeId',
                'type' => 'select',
                'label' => '* Income',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $incomeChoices,
                'required' => true,
            );

            $languageChoices = $userAttributes->get($this->config->language, false, 'id as value, name as label');
            //array_unshift($languageChoices, array('value' => '--', 'label' => 'Choose'));

            $form['languageId'] = array(
                'name' => 'languageId',
                'type' => 'checkbox',
                'label' => '* Language',
                'value' => ((isset($user->languageId)) ? $user->languageId : array()),
                'choices' => $languageChoices,
                'required' => true,
            );

            $bodyTypeChoices = $userAttributes->get($this->config->bodyType, false, 'id as value, name as label');
            array_unshift($bodyTypeChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->bodyTypeId)) ? $user->bodyTypeId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->bodyType, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['bodyTypeId'] = array(
                'name' => 'bodyTypeId',
                'type' => 'select',
                'label' => '* Body Style',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $bodyTypeChoices,
                'required' => true,
            );

            $userHightsList = array(array('value' => '--', 'label' => 'Choose'));
            for ($i = 54; $i <= 96; $i++) {
                $userHightsList[] = array('value' => $i, 'label' => ((int)($i / 12) . "' " . ($i % 12) . "\" (" . round($i * 2.54) . " cm)"));
            }
            $form['userHight'] = array(
                'name' => 'userHight',
                'type' => 'select',
                'label' => '* Height',
                'value' => ((isset($user->userHight)) ? $user->userHight : '--'),
                'valLabel' => ((isset($user->userHight)) ? $user->userHight : 'Choose'),
                'choices' => $userHightsList,
                'required' => true,
            );

            $hairLengthChoices = $userAttributes->get($this->config->hairLength, false, 'id as value, name as label');
            array_unshift($hairLengthChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->hairLengthId)) ? $user->hairLengthId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->hairLength, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['hairLengthId'] = array(
                'name' => 'hairLengthId',
                'type' => 'select',
                'label' => '* Hair style',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $hairLengthChoices,
                'required' => true,
            );

            $hairColorChoices = $userAttributes->get($this->config->hairColor, false, 'id as value, name as label');
            array_unshift($hairColorChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->hairColorId)) ? $user->hairColorId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->hairColor, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['hairColorId'] = array(
                'name' => 'hairColorId',
                'type' => 'select',
                'label' => '* Hair color',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $hairColorChoices,
                'required' => true,
            );

            $eyesColorChoices = $userAttributes->get($this->config->eyesColor, false, 'id as value, name as label');
            array_unshift($eyesColorChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->eyesColorId) and $user->eyesColorId != 'NULL') ? $user->eyesColorId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->eyesColor, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['eyesColorId'] = array(
                'name' => 'eyesColorId',
                'type' => 'select',
                'label' => '* Eyes color',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $eyesColorChoices,
                'required' => true,
            );

            $smokingChoices = $userAttributes->get($this->config->smoking, false, 'id as value, name as label');
            array_unshift($smokingChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->smokingId)) ? $user->smokingId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->smoking, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['smokingId'] = array(
                'name' => 'smokingId',
                'type' => 'select',
                'label' => '* Smoking',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $smokingChoices,
                'required' => true,
            );

            $drinkingChoices = $userAttributes->get($this->config->drinking, false, 'id as value, name as label');
            array_unshift($drinkingChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->drinkingId)) ? $user->drinkingId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->drinking, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['drinkingId'] = array(
                'name' => 'drinkingId',
                'type' => 'select',
                'label' => '* Drinking',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $drinkingChoices,
                'required' => true,
            );

            $form['userAboutMe'] = array(
                'name' => 'userAboutMe',
                'type' => 'textarea',
                'label' => '* About Me',
                'value' => ((isset($user->userAboutMe)) ? $user->userAboutMe : ''),
                'required' => true,
            );

            $form['userLookingFor'] = array(
                'name' => 'userLookingFor',
                'type' => 'textarea',
                'label' => '* Looking For',
                'value' => ((isset($user->userLookingFor)) ? $user->userLookingFor : ''),
                'required' => true,
            );

            $healthChoices = $userAttributes->get($this->config->health, false, 'id as value, name as label');
            array_unshift($healthChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->healthId)) ? $user->healthId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->health, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['healthId'] = array(
                'name' => 'healthId',
                'type' => 'select',
                'label' => '* Life Challenge',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $healthChoices,
                'required' => true,
            );

            $mobilityChoices = $userAttributes->get($this->config->mobility, false, 'id as value, name as label');
            array_unshift($mobilityChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->mobilityId)) ? $user->mobilityId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->mobility, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['mobilityId'] = array(
                'name' => 'mobilityId',
                'type' => 'select',
                'label' => '* Mobility',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $mobilityChoices,
                'required' => true,
            );
            //not requered
            $appearanceChoices = $userAttributes->get($this->config->appearance, false, 'id as value, name as label');
            array_unshift($appearanceChoices, array('value' => '--', 'label' => 'Choose'));
            $val = ((isset($user->appearanceId)) ? $user->appearanceId : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $valChoose = $userAttributes->get($this->config->appearance, array($val), 'id as value, name as label');
                $valLabel = $valChoose['label'];
            }
            $form['appearanceId'] = array(
                'name' => 'appearanceId',
                'type' => 'select',
                'label' => 'Appearance',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $appearanceChoices,
                'required' => false,
            );

            $userWeightsList = array(array('value' => '--', 'label' => 'Choose'));
            for ($i = 80; $i <= 400; $i += 2) {
                $kg = (int)($i * 0.45359237);
                $mg = ($i * 0.45359237 - $kg);
                $mg = ($mg < 0.25) ? 0 : ($mg > 0.75 ? 1 : 0.5);
                $kg = $kg + $mg;
                $userWeightsList[] = array('value' => $i, 'label' => ($i . " lbs (" . $kg . " kg)"));
            }
            $val = ((isset($user->userWeight)) ? $user->userWeight : '--');
            $valLabel = 'Choose';
            if ($val != '--') {
                $val = (int)$val;
                $kg = (int)($val * 0.45359237);
                $mg = ($val * 0.45359237 - $kg);
                $mg = ($mg < 0.25) ? 0 : ($mg > 0.75 ? 1 : 0.5);
                $kg = $kg + $mg;
                $valLabel = $val . " lbs (" . $kg . " kg)";
            }
            $form['userWeight'] = array(
                'name' => 'userWeight',
                'type' => 'select',
                'label' => 'Weight',
                'value' => $val,
                'valLabel' => $valLabel,
                'choices' => $userWeightsList,
                'required' => false,
            );

//            $form['userHobbies'] = array(
//                'name' => 'userHobbies',
//                'type' => 'text',
//                'label' => 'Custom Hobbies',
//                'value' => ((isset($user->userHobbies)) ? $user->userHobbies: ''),
//                'required' => false,
//            );

            $hobbyChoices = $userAttributes->get($this->config->hobby, false, 'id as value, name as label');
            $form['hobbyIds'] = array(
                'name' => 'hobbyIds',
                'type' => 'checkbox',
                'label' => 'Hobbies',
                'value' => ((isset($user->hobbyIds)) ? $user->hobbyIds : array()),
                'choices' => $hobbyChoices,
                'required' => false,
            );

            $characteristicChoices = $userAttributes->get($this->config->characteristic, false, 'id as value, name as label');
            $form['characteristicIds'] = array(
                'name' => 'characteristicIds',
                'type' => 'checkbox',
                'label' => 'Characteristics',
                'value' => ((isset($user->characteristicIds)) ? $user->characteristicIds : array()),
                'choices' => $characteristicChoices,
                'required' => false,
            );

            $lookingForChoices = $userAttributes->get($this->config->lookingFor, false, 'id as value, name as label');
            $form['lookingForIds'] = array(
                'name' => 'lookingForIds',
                'type' => 'checkbox',
                'label' => 'Looking For',
                'value' => ((isset($user->lookingForIds)) ? $user->lookingForIds : array()),
                'choices' => $lookingForChoices,
                'required' => false,
            );
        }


        return $form;
    }

    public function register($user, $passwordHasher)
    {
        $userAttributes = new UserAttributes();
        $err = array();
        $texts = array(
            'errText' => 'Please fill the missing details',
            'title' => 'General Information',
        );
        $form = array();
        if (isset($user->step)) {
            if ($user->step == 1) {
                if (isset($user->getRegions) and $user->getRegions == 1 and !empty($user->countryCode) and $user->countryCode != '--') {
                    $country = $userAttributes->get($this->config->countries, ['', $user->countryCode], 'id,code,name');
                    $regionCodeChoices = $userAttributes->get($this->config->regions_, array('', $country['id']), 'code as value, name as label');
                    $defaultVal = false;
                    if (isset($regionCodeChoices['label'])) {
                        $regionCodeChoices = array($regionCodeChoices);
                    }
                    foreach ($regionCodeChoices as $i => $regionCodeChoice) {
                        if ($regionCodeChoice['label'] == '--') {
                            $regionCodeChoices[$i]['label'] = 'Choose';
                            $defaultVal = true;
                        }
                    }
                    if (!$defaultVal) {
                        array_unshift($regionCodeChoices, array('value' => '--', 'label' => 'Choose'));
                    }
                    $form['regionCode'] = array(
                        'name' => 'regionCode',
                        'type' => 'select',
                        'label' => (($user->countryCode == 'US') ? 'State' : 'Region'),
                        'value' => '--',
                        'valLabel' => 'Choose',
                        'choices' => $regionCodeChoices,
                        'required' => true,
                    );
                    if ($user->countryCode == 'US') {
                        $form['zipCode'] = array(
                            'name' => 'zipCode',
                            'type' => 'text',
                            'label' => 'Zip Code',
                            'value' => ((isset($user->zipCode)) ? $user->zipCode : ''),
                            'required' => true,
                        );
                    }
                    return [
                        'form' => $form
                    ];
                }
            }
            if ($user->step == 2) {
                $texts['title'] = 'Personal Details';
            }

            $err = $this->validateData($user);

            if (count($err) == 0 and $user->step == 2) {
                //add user
                $usersRepo = $this->em->getRepository('User');
                $user->notApproved = $usersRepo->isBadUser($user) ? 1 : 0;
                $newUser = new \App\Entity\User();
                $encodedPassword = $passwordHasher->hashPassword($newUser, $user->userPass);
                $newUser->setPassword($encodedPassword);
                $newUser->setMsEnter($user->userPass);
                $newUser->setEmail($user->userEmail);
                $newUser->setUsername($user->userNick);
                $newUser->setIp($_SERVER['REMOTE_ADDR']);
                $newUser->setGender($this->em->getRepository('App:Gender')->find($user->userGender));
                $newUser->setSexPref($this->em->getRepository('App:Sexpref')->find($user->sexPrefId));
                $newUser->setBirthday(new \DateTime($user->userBirthday->y . "-" . $user->userBirthday->m . "-" . $user->userBirthday->d . " 00:00:00"));
                $newUser->setZipCode($user->zipCode);
                $newUser->setCountry($this->em->getRepository('App:LocCountries')->findOneBy(['code'=>$user->countryCode]));
                $newUser->setRegion($this->em->getRepository('App:LocRegions')->findOneBy(['code'=>$user->regionCode, 'country'=>$newUser->getCountry()]));
                $newUser->setCity($this->em->getRepository('App:LocCities')->findOneBy(['name'=>$user->userCityName, 'region'=>$newUser->getRegion()]));
                $newUser->setCountryOfOrigin($this->em->getRepository('App:LocCountries')->findOneBy(['code'=>$user->countryOfOriginCode]));
                $newUser->setAboutMe($user->userAboutMe);
                $newUser->setLookingFor($user->userLookingFor);
                $newUser->setHealth($this->em->getRepository('App:Health')->find($user->healthId));
                $newUser->setMobility($this->em->getRepository('App:Mobility')->find($user->mobilityId));
                $newUser->setFirstName($user->userfName);
                $newUser->setLastName($user->userlName);
                $newUser->setMaritalStatus($this->em->getRepository('App:Maritalstatus')->find($user->maritalStatusId));
                $newUser->setChildren((int)$user->userChildren);
                $newUser->setEthnicOrigin($this->em->getRepository('App:Ethnicorigin')->find($user->ethnicOriginId));
                $newUser->setReligion($this->em->getRepository('App:Religion')->find($user->religionId));
                $newUser->setEducation($this->em->getRepository('App:Education')->find($user->educationId));
                $newUser->setOccupation($this->em->getRepository('App:Occupation')->find($user->occupationId));
                $newUser->setIncome($this->em->getRepository('App:Income')->find($user->incomeId));
                $newUser->setBodyType($this->em->getRepository('App:Bodytype')->find($user->bodyTypeId));
                $newUser->setHeight((int)$user->userHight);
                $newUser->setBodyType($this->em->getRepository('App:Bodytype')->find($user->bodyTypeId));
                $newUser->setHairLength($this->em->getRepository('App:Hairlength')->find($user->hairLengthId));
                $newUser->setHairColor($this->em->getRepository('App:Haircolor')->find($user->hairColorId));
                $newUser->setEyesColor($this->em->getRepository('App:Eyescolor')->find($user->eyesColorId));
                $newUser->setSmoking($this->em->getRepository('App:Smoking')->find($user->smokingId));
                $newUser->setDrinking($this->em->getRepository('App:Drinking')->find($user->drinkingId));
                $newUser->setAppearance($this->em->getRepository('App:Appearance')->find($user->appearanceId));
                $newUser->setWeight((int)$user->userWeight);
                foreach ($user->hobbyIds as $hobbyId) {
                    $newUser->addHobby($this->em->getRepository('App:Hobby')->find($hobbyId));
                }
                foreach ($user->languageId as $language) {
                    $newUser->addLanguage($this->em->getRepository('App:Language')->find($language));
                }
                foreach ($user->characteristicIds as $characteristicId) {
                    $newUser->addCharacteristic($this->em->getRepository('App:Characteristic')->find($characteristicId));
                }
                foreach ($user->lookingForIds as $lookingForId) {
                    $userAttributes->post($this->config->userLookingFors, array($user->userId, $lookingForId));
                    $newUser->addLookingfor($this->em->getRepository('App:Lookingfor')->find($lookingForId));
                }
                $newUser->setIsNotComplitedRegistration(false);
                $newUser->setRegistrationDate(new \DateTime());
                $newUser->setIsNotActivated(true);
                $newUser->setIsBlocked(false);
                $newUser->setIsFrozen(false);
                $newUser->setPoints(1);
                $newUser->setIsAdminMarked(false);
                $newUser->setIsNotApproved(false);
                $newUser->setRole($this->em->getRepository('App:Role')->find(3));
                $newUser->setIsPushOnNewMess(true);
                $newUser->setIsGetMsgToEmail(true);
                $this->em->persist($user);


                $user->userId = $newUser->getId();

                $user->newPass = $user->userPass;
                $mail = $this->sendMail($user);


                $user->userNotActivated = $newUser->getIsNotActivated();
                //$user->photo = $this->config->users->storage->defaultImg. '/images/' . (($user->userGender == 1) ? 'no_photo_female' : 'no_photo_male') . '.jpg';
                $user->photo = $newUser->getNoPhoto();
                //return json_encode(array('userId' => $result['userId'], 'err' => $err));
            }
            if (count($err) == 0 and $user->step == 1) {
                $user->step = 2;
            }
            if (isset($user->stepBack) and $user->stepBack == 1) {
                $user->step = 1;
            }
        }
        $form = $this->getForm('register', $user);

        return [
            "user" => $user,
            "error" => $err,
            'texts' => $texts,
            'form' => $form
        ];
    }

    public function sendMail($user, $templates = ['activation', 'welcome']){
        $code = $user->userId;
        foreach ($templates as $template) {
            $tempEntity = $this->getDoctrine()->getRepository('App:LangDyncpages')->findOneBy(['name' => $template]);

            $find = array('{HTTP_HOST}', '{CODE}', '{EMAIL}', '{USERPASS}');
            $replace = array($user->httpHost, $code, $user->userEmail, $user->userPass);

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: AdminD4D <office@dating4disabled.com>' . "\r\n";

            $text = str_replace($find, $replace, $tempEntity->getBody());
            $res = mail($user->userEmail, $tempEntity->getTitle(), $text, $headers);
        }
        return true;
    }

    public function setUserSubscription($user, $monthsNumber, $req = false){
        if($user->isPaying()){
            return true;
        }

        if($monthsNumber > 0 and $monthsNumber <= 14){
            $itunnesReq = ($req === false) ? array() : (array)json_decode($req);
            $strtotime = (isset($itunnesReq['date'])) ? new DateTime($itunnesReq['date']) : new DateTime();
            $strtotime = $strtotime->getTimestamp();
            $startDate = date("Y-m-d H:i:s", $strtotime);


            if($monthsNumber == 14){
                $endDate = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s", strtotime($startDate)) . " +" . (int)$monthsNumber . " days"));
            }else{
                $endDate = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s", strtotime($startDate)) . " +" . (int)$monthsNumber . " month"));
            }

            if(strtotime("now") > strtotime($endDate)){
                return false;
            }

            $startDate = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s", strtotime($startDate)) . '-1 day'));
            $userId = $user->getId();
            $sql = "UPDATE user SET paid_start_date=?, paid_end_date=? WHERE d=?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $startDate);
            $stmt->bindParam(2, $endDate);
            $stmt->bindParam(3, $userId, PDO::PARAM_INT);
            $userPay = $stmt->execute();

            if($req !== false){
                $name = 'IOS subscribe userId:' . $userId;
                $sql1 = "INSERT INTO data_save  (name, value) VALUES (?, ?)";
                $stmt1 = $this->db->prepare($sql1);
                $stmt1->bindParam(1, $name);
                $stmt1->bindParam(2, $req);

                $stmt1->execute();
            }

            $productId = ($monthsNumber == 3) ? 2 : 1;
            $paymentName = 'iTunes';

            $tranzilaIndex = ($req === false) ? 0 : $itunnesReq['transactionId'];
            $sql2 = "INSERT INTO payments_payment (date, product_id, user_id, num_of_months, paymentName, end_date, tranzila_index) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->bindParam(1, $startDate);
            $stmt2->bindParam(2, $productId);
            $stmt2->bindParam(3, $userId);
            $stmt2->bindParam(4, $monthsNumber);
            $stmt2->bindParam(5, $paymentName);
            $stmt2->bindParam(6, $endDate);
            $stmt2->bindParam(7, $tranzilaIndex);
            $stmt2->execute();



            if($userPay){
                return true;
            }
        }
        else return false;
    }

    public function reportAbuse($user, $profileId, $abuseMessage){

        //$userData = $this->getUserData($userId);

        $subject = "D4D App | Report Abuse - " . $profileId;

        $text = '
			User: ' . $profileId . '<br />
			Message: ' . $abuseMessage . '<br /><br />
			From user: ' . $user->getId() . '
		';

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $user->getUsername() . ' <' . $user->getEmail() . '>' . "\r\n";
        mail('office@dating4disabled.com',$subject,$text,$headers);
        return true;
    }

    public function sendMessageToAdmin($userId, $message, $email){

        if((int)$userId == 0){

        }
        $subject = "D4D App | Contact Us | " . (((int)$userId == 0) ? $email : $userId);
        $sql = "SELECT email FROM user WHERE id = :userId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->execute();
        $user = $stmt->fetch();

        $text = '
			Message: ' . $message . '<br /><br />
			User E-mail: ' . ((!empty($email)) ? $email : $user['userEmail']) .
            (((int)$userId > 0) ? ('<br />
			User ID: ' . $userId) : '') . '
		';

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . ((!empty($email)) ? $email : $user['userEmail']) . ' <' . ((!empty($email)) ? $email : $user['userEmail']) . '>' . "\r\n";
        mail('office@dating4disabled.com',$subject,$text,$headers);

        return true;
    }

    public function getUsersForLikes($user, $supposedToBeLiked, $notifId){
        $result = array();
        $userGender = $user->getGender()->getId();
        $user_sex_pref = $user->getSexPref()->getId();
        $userId = $user->getId();

        $add_where = '';

        // genders: 1 = man; 2 = woman; 3 = other;
        // orientations: 1 = straight; 2 = bi; 3 = lesbian; 4 = Curious; 5 = not say; 6 = devotee;
        //               7 = gay; 8 = asexual;

        //  2, 4, 5, 8


        if ($user_sex_pref == 1 || $user_sex_pref == 6) {
            $add_where .= ' AND u.gender_id NOT IN ('.$user_sex_pref.', 0) AND u.sex_pref_id NOT IN (7, 3, 5)';
        } else if ($user_sex_pref == 3 || $user_sex_pref == 7) {
            $add_where .= ' AND (u.gender_id = ' . $userGender . ' AND u.sex_pref_id NOT IN (1, 6))
                            OR (u.gender_id = 3 AND u.sex_pref_id = ' . $user_sex_pref . ')';
        } else {
            $add_where .= ' AND u.sex_pref_id NOT IN (1, 6) ';
        }

        //$lastEnter = ($this->getLastActivityAt() !== NULL) ? $this->getLastActivityAt() : $this->getLastVisitDate()
        //online = (last_activity_at > (NOW() - INTERVAL (CAST((SELECT value FROM admin_properties WHERE name = 'sessionTime') AS int) MINUTE) or last_visit_date > (NOW() - INTERVAL (CAST((SELECT value FROM admin_properties WHERE name = 'sessionTime') AS int) MINUTE))

        //age = DATE_FORMAT(FROM_DAYS(DATEDIFF(now(),YourDateofBirth)), '%Y')+0
        $sql = "
			SELECT
				u.id as id, u.username as nickName, CONCAT(u.id,'/',i.id) as imageId, i.ext as imageExt, (DATE_FORMAT(FROM_DAYS(DATEDIFF(now(),u.birthday)), '%Y')+0) as age 
           FROM
				user u
			JOIN
				images i
				ON u.id = i.user_id
			WHERE (last_activity_at > (NOW() - INTERVAL (SELECT value FROM admin_properties WHERE name = 'sessionTime') MINUTE) or last_visit_date > (NOW() - INTERVAL (SELECT value FROM admin_properties WHERE name = 'sessionTime') MINUTE))
			AND u.gender_id != :genderId
			AND u.is_blocked = 0
			AND u.is_frozen = 0
			AND i.validated = 1
			AND i.main = 1
			AND NOT EXISTS (SELECT user_from_id FROM like_me lm WHERE user_from_id = u.id AND user_to_id = :userId)
			AND NOT EXISTS (SELECT user_to_id FROM like_me WHERE is_bingo = 1 AND user_to_id = u.id AND user_from_id = :userId)
			ORDER BY RAND() LIMIT 200
		";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['genderId'=>$userGender,'userId'=>$userId]);
        $users = $stmt->fetchAll();
        if(count($users) < 200){
            $sql = "
				SELECT
					u.id as id, u.username as nickName, CONCAT(u.id,'/',i.id) as imageId, i.ext as imageExt, (DATE_FORMAT(FROM_DAYS(DATEDIFF(now(),u.birthday)), '%Y')+0) as age
				FROM
					user u
				JOIN
					images i
					ON u.id = i.user_id
				WHERE ((u.last_activity_at <= (NOW() - INTERVAL (SELECT value FROM admin_properties WHERE name = 'sessionTime') MINUTE) OR u.last_activity_at IS NULL) AND (u.last_visit_date <= (NOW() - INTERVAL (SELECT value FROM admin_properties WHERE name = 'sessionTime') MINUTE) OR u.last_visit_date IS NULL))
				AND u.gender_id != :genderId
				AND u.is_blocked = 0
				AND u.is_frozen = 0
				AND i.validated = 1
				AND i.main = 1
				AND NOT EXISTS (SELECT user_from_id FROM like_me WHERE user_from_id = u.id AND user_to_id = :userId)
				AND NOT EXISTS (SELECT user_to_id FROM like_me WHERE is_bingo = 1 AND user_to_id = u.id AND user_from_id = :userId)
				ORDER BY RAND() LIMIT 200
			";

            $stmt = $this->db->prepare($sql);
//            $stmt->bindParam(1,$userGender);
//            $stmt->bindParam(2,$userId);
//            $stmt->bindParam(3,$userId);
            //$stmt1->bindParam(3,$userId);
            $stmt->execute(['genderId'=>$userGender,'userId'=>$userId]);
            $users1 = $stmt->fetchAll();

            $users = array_merge($users, $users1);
        }


        if(!empty($supposedToBeLiked)){
            $sql = "
				SELECT
					u.id as id, u.username as nickName, CONCAT(u.id,'/',i.id) as imageId, i.ext as imageExt, (DATE_FORMAT(FROM_DAYS(DATEDIFF(now(),u.birthday)), '%Y')+0) as age
				FROM
					user u
				JOIN
					images i
					ON u.id = i.user_id
				WHERE u.id = :id
				AND u.is_blocked = 0
				AND u.is_frozen = 0
				AND i.validated = 1
				AND i.main = 1
				AND NOT EXISTS (SELECT user_from_id FROM like_me WHERE user_from_id = u.id AND user_to_id = :userId)
				AND NOT EXISTS (SELECT user_to_id FROM like_me WHERE is_bingo = 1 AND user_to_id = u.id AND user_from_id = :userId)
			";

            $stmt = $this->db->prepare($sql);
//            $stmt->bindParam(1,$supposedToBeLiked);
//            $stmt->bindParam(2,$userId);
//            $stmt->bindParam(3,$userId);
            $stmt->execute(['id'=>$supposedToBeLiked,'userId'=>$userId]);
            $user = $stmt->fetch();
            array_unshift($users, $user);

            $this->setUserNotificationAsRead($notifId);
        }

        return [
            "notifId" => $notifId,
            //"sql" => $sql,
            "itemsNumber" => count($users),
            "imagesStoragePath" => $this->config->users->storage->images,
            "items" => $users,
        ];

    }

    public function setUserNotificationAsRead($notifId){
        $sql = "UPDATE user_notifications SET is_read = 1 WHERE id = '" . $notifId . "'";
        $this->db->query($sql);
    }

    public function doLike($userId, $currentUserId){

        $sql = "SELECT id FROM like_me WHERE user_from_id = ? AND user_to_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $currentUserId);
        $stmt->bindParam(2, $userId);
        $stmt->execute();
        $like = $stmt->fetch();

        if(!empty($like['id'])){
            $sql = "UPDATE like_me SET is_bingo = 1 WHERE id = '" . $like['id'] . "'";
            $this->db->query($sql);

            $sql = "INSERT INTO user_notifications (like_me_id, notification_id, date, is_read, user_id) VALUES (?, 2, GETDATE(), 0, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $like['id']);
            $stmt->bindParam(2, $userId);
            $stmt->execute();

            $sql = "SELECT email, username FROM user WHERE (id = ? AND is_blocked = 0) OR (id = ? AND is_blocked = 0)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $userId);
            $stmt->bindParam(2, $currentUserId);
            $stmt->execute();
            $users = $stmt->fetchAll();
            foreach ($users as $row){
                $this->sendBingoEmail($row['email'], $row['username']);
            }
//            $this->sendBingoEmail($users[0]['userEmail'], $users[1]['userNic']);
//            $this->sendBingoEmail($users[1]['userEmail'], $users[0]['userNic']);

            return array("bingo" => true);
        }

        $sql = "INSERT INTO like_me (user_from_id, user_to_id, is_bingo, is_show_splash_from, is_show_splash_to) VALUES (?, ?, 0, 0, 0)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $userId);
        $stmt->bindParam(2, $currentUserId);
        $stmt->execute();
        $lastId = $this->db->lastInsertId();

        $sql = "INSERT INTO user_notifications (like_me_id, notification_id, date, is_read, user_id) VALUES (?, 1, GETDATE(), 0, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $lastId);
        $stmt->bindParam(2, $userId);
        $stmt->execute();

        return array("like" => true);
    }

    public function sendBingoEmail($userEmail, $userNick){

        $subject = "Dating4Disabled Bingo is waiting for you :)";
        $text = '
			<div>
			' . $userNick . ' likes you too!
			<br><br>
			<a href="http://' . $this->config->application->domain  . '">
			Entrance through mobile version of Dating4Disabled Click here
			</a>
			<br><br>
			Or open the app on your mobile device Dating4Disabled and wait for a surprise
			<br><br>
			Good luck
			<br><br>
			Team Dating4Disabled
			<br><br>
			<a href="https://dating4disabled.com">dating4disabled.com</a>
			</div>';

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: office@dating4disabled.com <office@dating4disabled.com>' . "\r\n";
        mail($userEmail,$subject,$text,$headers);

    }

    public function getLikesNotifications($userId){
        //CONVERT(varchar, un.date, 103) as date
        $sql = "
			SELECT
				un.user_id as userId, un.date, un.is_read as isRead, un.notification_id as notificationId, lm.is_bingo as bingo, u.id, u.username as nickName, n.template, CONCAT(u.id,'/',i.id) as imageId, i.ext as imageExt
			FROM
				user_notifications un
			JOIN
				like_me lm
				ON un.like_me_id = lm.id
			JOIN
				user u
				ON u.id = CASE
					WHEN lm.user_from_id = un.user_id
					THEN lm.listMemberId
					ELSE lm.listOwnerId
				END
			JOIN
				notifications n
				ON un.notification_id = n.id
			RIGHT JOIN
				images i
				ON u.uid = i.user_id AND i.validated = 1 AND i.main = 1
			WHERE
				un.user_id = ? AND u.is_blocked = 0 AND u.is_frozen = 0
			ORDER BY
				un.is_read ASC, un.date DESC
		";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1,$userId);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return array(
            "imagesStoragePath" => $this->config->users->storage->images,
            //"imagesExtension" => "jpg",
            "itemsNumber" => count($result),
            "items" => $result,
        );

    }

    public function getUserBingo($userId){

        $sql = "
			SELECT lm.id, lm.user_from_id, u.id, u.username as nickName, CONCAT(i1.user_id,'/',i1.id) as userImageId_1, i1.ext as imageExt_1, CONCAT(i2.user_id,'/',i2.id) as userImageId_2, i2.ext as imageExt_2 FROM like_me lm
			JOIN
				user u
				ON u.id = CASE
					WHEN lm.user_from_id = ?
					THEN lm.user_to_id
					ELSE lm.user_from_id
				END
			RIGHT JOIN
				images i1
				ON i1.user_id = ?  AND i1.validated = 1 AND i1.main = 1
			RIGHT JOIN
				images i2
				ON i2.user_id = u.id AND i2.validated = 1 AND i2.main = 1
			WHERE ((user_from_id = ? AND is_bingo = 1 AND is_show_splash_from = 0) OR (user_to_id = ? AND is_bingo = 1 AND is_show_splash_to = 0))
				AND u.is_blocked = 0 AND u.is_frozen = 0
		";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $userId);
        $stmt->bindParam(2, $userId);
        $stmt->bindParam(3, $userId);
        $stmt->bindParam(4, $userId);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return array(
            "imagesStoragePath" => $this->config->users->storage->images,
            //"imagesExtension" => "jpg",
            "itemsNumber" => count($result),
            "items" => $result,
        );
    }

    public function setBingoAsSplashed($userId, $bingo){

        $fieldName = ($bingo->user_from_id == $userId) ? "is_show_splash_from" : "is_show_splash_to" ;
        $id = $bingo->id;

        $sql = "UPDATE like_me SET " . $fieldName . " = 1 WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return true;

    }
}
