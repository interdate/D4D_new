<?php
namespace AppBundle\Services;

use AppBundle\Entity\Payment;
use AppBundle\Entity\PaymentHistory;
use AppBundle\Entity\Photo;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Transfer
{
    public $em;
    public $container;
    public $user;
    public $transferStatus;
    public $admin;
    public $quick = false;

    public function __construct(EntityManager $entityManager, $container)
    {
        $this->em = $entityManager;
        $this->container = $container;
    }

    public function setQuick($quick){
        $this->quick = $quick;
    }

    public function callAPI($method, $url, $data){
        $curl = curl_init();
        if ($data){
            $data = json_encode($data);
        }
        switch ($method){
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'API: interdatezigzug',
            'Content-Type: application/json',
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        // EXECUTE:
        $result = curl_exec($curl);
        if(!$result){die("Connection Failure");}
        curl_close($curl);
        $res = (array)json_decode(($result));
        return $res;
    }

    public function foundUser($username){
        $phone = preg_replace('/\D/', '', $username);
        if (substr($phone, 0, 1) == '0') {
            $phone = '972' . substr($phone, 1);
        }
        if (substr($phone, 0, 3) != '972') {
            $phone = '972' . $phone;
        }
        if (substr($phone, 0, 4) == '9720') {
            $phone = '972' . substr($phone, 4);
        }
        if($phone == '972' or strlen($phone) != 12){
            $phone = '--none--';
        }
        $sql = "SELECT userId
                FROM users 
                WHERE userEmail = '$username' OR userNick='$username'"; //WHERE userId = ?  OR userPhone = '$phone'
        //905681356 - admin
        $params = array();
        $res = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
            array(
                'sql' => $sql,
                'params' => $params
            )
        );
        //var_dump($res);die;
        if(count($res) == 0){
            return null;
        }
        $user = $this->updateUserByMs(array('userId' => $res[0]->userId), true);
        //var_dump($user->getId());
        $this->setUser($user);
        return $user;
    }

    public function updateData(){
        $res = false;
        // return $this->transferStatus;
        // var_dump($this->transferStatus);die;

//        if($this->user and $this->user->getId() == 24578){
//            var_dump($this->transferStatus);die;
//        }
        if($this->transferStatus and $this->user){
            //var_dump($this->transferStatus);die;
            // var_dump($this->transferStatus->messages, ($this->transferStatus->messages == 'done' and $this->transferStatus->likes == 'done'));die;
            if($this->transferStatus->messages === 'done' and $this->transferStatus->likes === 'done' and $this->transferStatus->lists === 'done'){
                $res = 'done';
            }else{
                if($this->transferStatus->messages !== 'done'){
                    $page = (int)$this->transferStatus->messages + 1;
                    $res = $this->uploadUserMessage($this->user, $page);
                    $this->transferStatus->messages = ($res == 'done') ? $res : $page;
                }elseif ($this->transferStatus->likes !== 'done'){
                    $page = (int)$this->transferStatus->likes + 1;
                    $res = $this->uploadUserLikes($this->user, $page);
                    $this->transferStatus->likes = ($res == 'done') ? $res : $page;
                }elseif ($this->transferStatus->lists !== 'done'){
                    $this->transferStatus->lists = $this->uploadUserLists($this->user);
                }

                $this->updateUserTransferStatus();

                $res = ($this->transferStatus->lists !== 'done') ? 'update' : 'done';
            }
        }
        return $res;
    }

    public function updateDataUsers($page = 0){
        $conn = $this->em->getConnection();
        // $this->em->getRepository('AppBundle:');
        $count = 100;
        $offset = $page * $count;
        $sql = "SELECT id, ms_id FROM user WHERE 1=1 ORDER BY id ASC LIMIT " . $offset . ", " . $count;
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll();
        // var_dump($users[0]['id']);
        foreach ($users as $i => $user){
            if($i == 27){
                //var_dump($user['id']);die;
            }
            $this->updateUserByMs(array('userId' => $user['ms_id']));
        }
        // var_dump($user['id']);die;
        return count($users);
    }


    public function updateUserByMs($criteria, $imgForse = false) { // array('userId', 'userEmail', 'userNick')

        $where = '';
        $params = array();
        foreach ($criteria as $key => $value){
            $where .= (!empty($where) ? ' AND ' : '') . 'u.' . $key . '=?';
            $params[] = $value;
        }
        $sql = "SELECT u.*, r.itemName as region,
                  h.itemName as hair, h1.itemName as hair1,
                  hs.itemName as hairStyle, hs1.itemName as hairStyle1,
                  e.itemName as eyes, e1.itemName as eyes1,
                  b.itemName as body, b1.itemName as body1,
                  ms.itemName as relationshipStatus,
                  c.itemName as country, c1.itemName as country1,
                  eth.itemName as ethnicity, eth1.itemName as ethnicity1,
                  s.itemName as sexPref, s1.itemName as sexPref1,
                  ex.itemName as experience, ex1.itemName as experience1,
                  sm.itemName as smoking, sm1.itemName as smoking1,
                  d.itemName as drinking, d1.itemName as drinking1,
                  dbo.isApproved(u.userId) as isApproved
                FROM users u  
                LEFT JOIN countryRegions r ON u.countryRegionId=r.itemId 
                LEFT JOIN users_hairColor h ON u.hairColorId0=h.itemId
                LEFT JOIN users_hairColor h1 ON u.hairColorId1=h1.itemId
                LEFT JOIN users_hairStyle hs ON u.hairStyleId0=hs.itemId
                LEFT JOIN users_hairStyle hs1 ON u.hairStyleId1=hs1.itemId
                LEFT JOIN users_eyesColor e ON u.eyesColorId0=e.itemId
                LEFT JOIN users_eyesColor e1 ON u.eyesColorId1=e1.itemId
                LEFT JOIN users_bodyType b ON u.bodyTypeId0=b.itemId
                LEFT JOIN users_bodyType b1 ON u.bodyTypeId1=b1.itemId
                LEFT JOIN users_maritalStatus ms ON u.maritalStatusId=ms.itemId
                LEFT JOIN users_country c ON u.countryOfOriginId0=c.itemId
                LEFT JOIN users_country c1 ON u.countryOfOriginId1=c1.itemId
                LEFT JOIN users_ethnicity eth ON u.ethnicityId0=eth.itemId
                LEFT JOIN users_ethnicity eth1 ON u.ethnicityId1=eth1.itemId
                LEFT JOIN users_sexPref s ON u.sexPrefId0=s.itemId
                LEFT JOIN users_sexPref s1 ON u.sexPrefId1=s1.itemId
                LEFT JOIN users_experience ex ON u.experienceId0=ex.itemId
                LEFT JOIN users_experience ex1 ON u.experienceId1=ex1.itemId
                LEFT JOIN users_smoking sm ON u.smokingId0=sm.itemId
                LEFT JOIN users_smoking sm1 ON u.smokingId1=sm1.itemId
                LEFT JOIN users_drinking d ON u.drinkingId0=d.itemId
                LEFT JOIN users_drinking d1 ON u.drinkingId1=d1.itemId
                WHERE " . $where; //WHERE userId = ?
         //905681356 - admin

        if(count($criteria) == 1 and isset($criteria['userId'])) {
            $sql = str_replace("userId=?", "userId=" . $criteria['userId'], $sql);
            $params = array();
        }
        $res = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
            array(
                'sql' => $sql,
                'params' => $params
            )
        );
        //var_dump($res);die;
        if(count($res) == 0 and empty($res[0]['userNick'])){
            return null;
        }

        $encoder = $this->container->get('security.password_encoder');
        $countInsert = 0;

        $row = (array)$res[0];

        $check = $this->em->getRepository('AppBundle:User')->findOneBy(array('msId' => $row['userId']));
        if (!$check) {

            $countInsert++;
            $entety = new User();
        } else {

            $entety = $check;
        }
        //dump($entety);
        if($row['isApproved'] == '1'){
            $entety->setVerifyCount(3);
        }
        $entety->setMsUpload(2);
        if ($entety->getId() != 3) {
            $entety->setRole($this->em->getRepository('AppBundle:Role')->find(2));
        }
        $entety->setMsId($row['userId']);

        $entety->setEmail($row['userEmail']);
        $entety->setMsEnter($row['userPass']);
        //var_dump($row['userNick']);die;
        if(empty($row['userNick'])){
            $row['userNick'] = $row['userNum'];
        }
        $checkUsername = $this->em->getRepository('AppBundle:User')->findOneBy(array('username' => $row['userNick']));
        if($checkUsername and $checkUsername->getMsId() != $row['userId']){
            $row['userNick'] .= '-1';
        }
        $entety->setUsername($row['userNick']);


        $encoded = $encoder->encodePassword($entety, $row['userPass']);
        $entety->setPassword($encoded);

        $genderId = ($row['userGender'] == '0') ? 3 : (int)$row['userGender'];
        $gender = $this->em->getRepository('AppBundle:Gender')->find($genderId);
        //var_dump($genderId);die;
        $entety->setGender($gender);
        $entety->setBirthday(new \DateTime($row['userBirthday0']));

        if ($row['userGender'] == '2') {
            $entety->setBirthday1(new \DateTime($row['userBirthday1']));
            $entety->setHeight1((int)$row['userHeight1']);
            if ($row['userWeight1'] != null) {
                $entety->setWeight1($row['userWeight1']);
            }
            if ($row['hair1'] != null) {
                $hair1 = $this->em->getRepository('AppBundle:Hair')->findOneBy(array('name' => $row['hair1']));
                if ($hair1) {
                    $entety->setHair1($hair1);
                }
            }
            if ($row['hairStyle1'] != null) {
                $hairStyle1 = $this->em->getRepository('AppBundle:HairStyle')->findOneBy(array('name' => $row['hairStyle1']));
                if(!$hairStyle1){
                    $hairStyle1 = $this->em->getRepository('AppBundle:HairStyle')->find(4);
                }
                if ($hairStyle1) {
                    $entety->setHairStyle1($hairStyle1);
                }
            }
            if ($row['eyes1'] != null) {
                $eyes = $this->em->getRepository('AppBundle:Eyes')->findOneBy(array('name' => $row['eyes1']));
                if ($eyes) {
                    $entety->setEyes1($eyes);
                }
            }
            if ($row['body1'] != null) {
                $body = $this->em->getRepository('AppBundle:Body')->findOneBy(array('name' => $row['body1']));
                if ($body) {
                    $entety->setBody1($body);
                }
            }
            if ($row['country1'] != null) {
                $country = $this->em->getRepository('AppBundle:Country')->findOneBy(array('name' => $row['country1']));
                if ($country) {
                    $entety->setCountryOfOrigin1($country);
                }
            }
            if ($row['ethnicity1'] != null) {
                $ethnicity = $this->em->getRepository('AppBundle:Ethnicity')->findOneBy(array('name' => $row['ethnicity1']));
                if ($ethnicity) {
                    $entety->setEthnicity1($ethnicity);
                }
            }
            if ($row['sexPref1'] != null) {
                $sexPref = $this->em->getRepository('AppBundle:SexPref')->findOneBy(array('name' => $row['sexPref1']));
                if ($sexPref) {
                    $entety->setSexPref1($sexPref);
                }
            }
            if ($row['experience1'] != null) {
                $experience = $this->em->getRepository('AppBundle:Experience')->findOneBy(array('name' => $row['experience1']));
                if ($experience) {
                    $entety->setExperience1($experience);
                }
            }
            if ($row['smoking1'] != null) {
                $smoking = $this->em->getRepository('AppBundle:Smoking')->findOneBy(array('name' => $row['smoking1']));
                if ($smoking) {
                    $entety->setSmoking1($smoking);
                }
            }
            if ($row['drinking1'] != null) {
                $drinking = $this->em->getRepository('AppBundle:Drinking')->findOneBy(array('name' => $row['drinking1']));
                if ($drinking) {
                    $entety->setDrinking1($drinking);
                }
            }
        }

        if ($row['userCity'] != null) {
            //$res123 = preg_match("/\p{Hebrew}/u", $row['userCity']);
            //echo preg_match("/[\u0591-\u05F4]/", $row['userCity']);
            //$rtl_chars_pattern = '/^[\x{0591}-\x{05f4}]/u';
            //$res123 = preg_match($rtl_chars_pattern, $row['userCity']);
            $row['userCity'] = str_replace('״', "יי", $row['userCity']);

            //var_dump(mb_detect_encoding('Нетания'),mb_detect_encoding($row['userCity']),$row['userCity']);die;

            foreach (array("¢", "₪", "©", "•") as $char){
                if(strpos($row['userCity'], $char) !== false){
                    $row['userCity'] = '';
                    break;
                }
            }
            if($row['userCity'] == "Ришон лецион" or $row['userCity'] == "ришон лецион"){
                $row['userCity'] = "ראשון לציון";
            }
            if($row['userCity'] == "бат ям"){
                $row['userCity'] = 'בת ים';
            }
            if($row['userCity'] == "Нетания"){
                $row['userCity'] = 'נתניה';
            }
            if($row['userCity'] == 'Ашдод'){
                $row['userCity'] = 'אשדוד';
            }
            if($row['userCity'] == 'хайфа'){
                $row['userCity'] = 'חיפה';
            }
            if($row['userCity'] == 'ת״א' or $row['userCity'] == 'תל אביב') {
                $row['userCity'] = "תל אביב-יפו";
            }
            if(preg_match('/[А-Яа-яЁё]/u', $row['userCity']) or $row['userCity'] == "׳¨׳׳׳”"){
                $row['userCity'] = '';
            }

            //var_dump($row['userCity']);die;
            $row['userCity'] = trim($row['userCity']);
            // var_dump($row['userCity']);die;
            $city = empty($row['userCity']) ? null : $this->em->getRepository('AppBundle:City')->findOneBy(array('name' => $row['userCity']));
            // var_dump($city);die;
            if($city) {
                $entety->setCity($city);
            }
        }

        if ($row['region'] != null) {
            $region = $this->em->getRepository('AppBundle:Region')->findOneBy(array('name' => $row['region']));
            if ($region) {
                $entety->setRegion($region);
            }
        }

        if($row['userAdminMarked'] != null) {
            $entety->setIsFlagged((boolean)$row['userAdminMarked']);
        }

        $entety->setHeight((int)$row['userHeight0']);
        if ($row['userWeight0'] != null) {
            $entety->setWeight($row['userWeight0']);
        }

        if ($row['hair'] != null) {
            $hair = $this->em->getRepository('AppBundle:Hair')->findOneBy(array('name' => $row['hair']));
            if ($hair) {
                $entety->setHair($hair);
            }
        }
        if ($row['hairStyle'] != null) {
            $hairStyle = $this->em->getRepository('AppBundle:HairStyle')->findOneBy(array('name' => $row['hairStyle']));
            if(!$hairStyle){
                $hairStyle = $this->em->getRepository('AppBundle:HairStyle')->find(4);
            }
            if ($hairStyle) {
                $entety->setHairStyle($hairStyle);
            }
        }
        if ($row['eyes'] != null) {
            $eyes = $this->em->getRepository('AppBundle:Eyes')->findOneBy(array('name' => $row['eyes']));
            if ($eyes) {
                $entety->setEyes($eyes);
            }
        }
        if ($row['body'] != null) {
            $body = $this->em->getRepository('AppBundle:Body')->findOneBy(array('name' => $row['body']));
            if ($body) {
                $entety->setBody($body);
            }
        }
        $children = ((int)$row['userChildren'] < 3) ? (int)$row['userChildren'] + 1 : 4;
        $entety->setChildren($this->em->getRepository('AppBundle:Children')->find($children));

        if ($row['country'] != null) {
            $country = $this->em->getRepository('AppBundle:Country')->findOneBy(array('name' => $row['country']));
            if ($country) {
                $entety->setCountryOfOrigin($country);
            }
        }
        if ($row['ethnicity'] != null) {
            $ethnicity = $this->em->getRepository('AppBundle:Ethnicity')->findOneBy(array('name' => $row['ethnicity']));
            if ($ethnicity) {
                $entety->setEthnicity($ethnicity);
            }
        }
        //sexPrefId0
        if ($row['sexPref'] != null) {
            $sexPref = $this->em->getRepository('AppBundle:SexPref')->findOneBy(array('name' => $row['sexPref']));
            if ($sexPref) {
                $entety->setSexPref($sexPref);
            }
        }
        //experienceId0
        if ($row['experience'] != null) {
            $experience = $this->em->getRepository('AppBundle:Experience')->findOneBy(array('name' => $row['experience']));
            if ($experience) {
                $entety->setExperience($experience);
            }
        }
        //smokingId0
        if ($row['smoking'] != null) {
            $smoking = $this->em->getRepository('AppBundle:Smoking')->findOneBy(array('name' => $row['smoking']));
            if ($smoking) {
                $entety->setSmoking($smoking);
            }
        }
        //drinkingId0
        if ($row['drinking'] != null) {
            $drinking = $this->em->getRepository('AppBundle:Drinking')->findOneBy(array('name' => $row['drinking']));
            if ($drinking) {
                $entety->setDrinking($drinking);
            }
        }
        //userAboutMe
        $about = ($row['userAboutMe'] == null) ? '' : $row['userAboutMe'];
        $entety->setAbout($about);
        //userLookingFor
        $looking = ($row['userLookingFor'] == null) ? '' : $row['userLookingFor'];
        //var_dump($row['userLookingFor']);die;
        $entety->setLooking($looking);
        //userIp
        $entety->setIp($row['userIp']);
        //userRegistrationDate
        $entety->setSignUpDate(new \DateTime($row['userRegistrationDate']));
        //userLastVisitDate
        $entety->setLastActivityAt(new \DateTime($row['userLastVisitDate']));
        $entety->setLastRealActivityAt(new \DateTime($row['userLastVisitDate']));
        //userNotActivated
        $isActivated = ((int)$row['userNotActivated'] == 1) ? 0 : 1;
        $entety->setIsActivated($isActivated);
        //userFrozen
        $entety->setIsFrozen((int)$row['userFrozen']);
        //userWhyFrozen
        if ($row['userWhyFrozen'] != null) {
            //var_dump($row['userWhyFrozen']);die;
            $entety->setFreezeReason($row['userWhyFrozen']);
        }
        //userBlocked
        $isActive = ((int)$row['userBlocked'] == 1) ? 0 : 1;
        $entety->setIsActive($isActive);
        //userPrePaidPoints
        $entety->setPoints((int)$row['userPrePaidPoints']);
        //userPaidStartDate
        if ($row['userPaidStartDate'] != null) {
            $entety->setStartSubscription(new \DateTime($row['userPaidStartDate']));
        }
        //userPaidEndDate
        if ($row['userPaidEndDate'] != null) {
            $entety->setEndSubscription(new \DateTime($row['userPaidEndDate']));
        }
        //userGetMsgToEmail
        $entety->setIsSentEmail((int)$row['userGetMsgToEmail']);
        //userAdminComment
        if ($row['userAdminComment'] != null) {
            $entety->setAdminComments($row['userAdminComment']);
        }
        //userAdminMarked

        //userSavedSearch
        if ($row['userSavedSearch'] != null) {
            parse_str($row['userSavedSearch'], $params);
            //var_dump($params);die;
            if(isset($params['userId']) and (int)$params['userId'] == 0){
                $params['userId'] = '';
            }
            if(isset($params['userNick'])){
                $params['userNick'] = '';
            }
//                $params['userNick'] = str_replace("�", "", $params['userNick']);
            //var_dump($params, $entety->getMsId());die;
            $entety->setSearchSave($params);
        }
        //userFrontPage
        $entety->setIsOnHomepage($row['userFrontPage']);

        //free_today
        if ($row['free_today'] != null) {
            $entety->setFreeToday(new \DateTime($row['free_today']));
        }
        //userCommercial
        $entety->setCommercial((int)$row['userCommercial']);
        //userApprovedAdmin

        //userPhone
        // var_dump($row['userPhone']);die;
        if ($row['userPhone']) {
            $phone = preg_replace('/\D/', '', $row['userPhone']);
            if (substr($phone, 0, 1) == '0') {
                $phone = '972' . substr($phone, 1);
            }
            if (substr($phone, 0, 3) != '972') {
                $phone = '972' . $phone;
            }
            if (substr($phone, 0, 4) == '9720') {
                $phone = '972' . substr($phone, 4);
            }
            $entety->setPhone($row['userPhone']);
        }
        //long
        if ($row['long'] != null) {
            $entety->setLongitude($row['long']);
        }
        //lat
        if ($row['lat'] != null) {
            $entety->setLatitude($row['lat']);
        }
        //newMessPushNotif
        $entety->setIsSentPush((int)$row['newMessPushNotif']);

        //$em->persist($entety);
        //$em->flush();

        //userLookingForGender
        $connection = $this->em->getConnection();
        foreach ($entety->getLookingForGender() as $gender) {
//            $entety->removeLookingForGender($gender);
//            $this->em->persist($entety);
//            $this->em->flush();

            $stmt = $connection->prepare("
                    DELETE FROM looking_gender
                    WHERE user_id = " . $entety->getId());
            $stmt->execute();
        }

        if ($row['userLookingForGender'] == 2 || $row['userLookingForGender'] == 3 || $row['userLookingForGender'] == 6 || $row['userLookingForGender'] == 7) {
            $entety->addLookingForGender($this->em->getRepository('AppBundle:Gender')->find(1));
        }
        if ($row['userLookingForGender'] == 4 || $row['userLookingForGender'] == 5 || $row['userLookingForGender'] == 6 || $row['userLookingForGender'] == 7) {
            $entety->addLookingForGender($this->em->getRepository('AppBundle:Gender')->findOneBy(array('id' => 2)));
        }

        if ($row['userLookingForGender'] == 1 || $row['userLookingForGender'] == 5 || $row['userLookingForGender'] == 7) {
            $entety->addLookingForGender($this->em->getRepository('AppBundle:Gender')->findOneBy(array('id' => 3)));
        }

        //addMeetingTime
        $sql = "SELECT uumt.*,umt.itemName FROM users_user_meetingTime uumt JOIN users_meetingTime umt ON uumt.itemId=umt.itemId WHERE uumt.userId=?";
        $params = array($entety->getMsId()); //905681356 - admin

        $meetingTimes = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
            array(
                'sql' => $sql,
                'params' => $params
            )
        );
        foreach ($entety->getMeetingTime() as $meetingT) {
            $entety->removeMeetingTime($meetingT);
//            $this->em->persist($entety);
//            $this->em->flush();

            $stmt = $connection->prepare("
                    DELETE FROM user_meeting
                    WHERE user_id = " . $entety->getId());
            $stmt->execute();
        }
        if (count($meetingTimes) > 0) {
            foreach ($meetingTimes as $meetingTime) {
                $meetingTime = (array)$meetingTime;
                $meeting = $this->em->getRepository('AppBundle:MeetingTime')->findOneBy(array('name' => $meetingTime['itemName']));
                if($meeting) {
                    $entety->addMeetingTime($meeting);
                }
            }
        }
        $this->em->persist($entety);
        $this->em->flush();

        $sql = "SELECT *
            FROM images
            WHERE userId = ?
            ORDER BY imgId ASC";
        $params = array($entety->getMsId());

        $resP = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
            array(
                'sql' => $sql,
                'params' => $params
            )
        );
        set_time_limit(300);
        $photo = 0;
        $userPhotos = $entety->getPhotos();
        if (count($resP) > 0 and ($imgForse)) {

            //var_dump($res,count($userPhotos));dump($userPhotos);die;
            if ($entety->getId() != 3) {
                if (count($userPhotos) > 0) {
                    foreach ($userPhotos as $photo) {

                        $entety->removePhoto($photo);
                        $this->em->remove($photo);
                        $this->em->flush();
                    }

                }

                foreach ($resP as $rowPhoto) {
                    $rowPhoto = (array)$rowPhoto;
                    $isMain = ($rowPhoto['imgMain'] == '1');
                    $isValid = ($rowPhoto['imgValidated'] == '1');
                    $photo = new Photo();
                    $photo->setUser($entety);

                    $tmpFile = $_SERVER['DOCUMENT_ROOT'] . '/images/'.$entety->getId().'.jpg';
                    //var_dump(file_exists('https://www.zigzug.co.il/images/users/original/' . $rowPhoto['imgId'] . '.jpg'));die;
                    $fileOld = ($this->file_isset('https://oldzigzug.wee.co.il/images/users/large/' . $rowPhoto['imgId'] . '.jpg')) ? 'https://oldzigzug.wee.co.il/images/users/large/' . $rowPhoto['imgId'] . '.jpg' :
                        ($this->file_isset('https://oldzigzug.wee.co.il/images/users/original/' . $rowPhoto['imgId'] . '.jpg') ? 'https://oldzigzug.wee.co.il/images/users/original/' . $rowPhoto['imgId'] . '.jpg' : 'https://oldzigzug.wee.co.il/images/users/small/' . $rowPhoto['imgId'] . '.jpg');
                    //sleep(1);
                    copy($fileOld, $tmpFile);
                    //var_dump($this->file_isset('https://www.zigzug.co.il/images/users/large/102595.jpg'));die;
                    $file = new UploadedFile($tmpFile, 'tmp.jpg', null, null, null, true);
                    $photo->setFile($file);
                    //$photo->upload();
                    $photo->setIsValid($isValid);
                    $photo->setIsMain($isMain);

                    $this->em->persist($photo);
                    $this->em->flush();


                    $entety->addPhoto($photo);
                    $this->em->persist($entety);
                    $this->em->flush();

                    //var_dump($rowPhoto, $photo->getWebPath());die;
                    $optimized = $this->applyFilterToPhoto('optimize_original', $photo->getWebPath());
                    $this->savePhoto($optimized, $photo->getAbsolutePath());

                    //die;
                    // unlink($tmpFile);
                    $photo++;
                }
            }
        }
        if(!$entety->isPaying()) {
            //paymentHistory
            $sql = "SELECT * 
          FROM payments_subscription
          WHERE userId=" . $entety->getMsId();
            $params = array();

            $resP = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
                array(
                    'sql' => $sql,
                    'params' => $params
                )
            );

            foreach ($resP as $i => $row) {
                $row = (array)$row;
                $this->savePaymentToUser($row);

            }
        }
        if($imgForse){
            $user_id = $entety->getId();
            $ms_id = $entety->getMsId();
            $username = $entety->getUsername();
            $email = $entety->getEmail();
            $messages = $likes = 0;
            $lists = 'black_0';
            $sql = "INSERT INTO  data_transfer (user_id, ms_id, username, email, messages, likes, lists) VALUES (?,?,?,?,?,?,?)";

            $stmt = $this->em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $user_id);
            $stmt->bindValue(2, $ms_id);
            $stmt->bindValue(3, $username);
            $stmt->bindValue(4, $email);
            $stmt->bindValue(5, $messages);
            $stmt->bindValue(6, $likes);
            $stmt->bindValue(7, $lists);
            $stmt->execute();
        }
        return $entety;
    }

    public function getUserPayment($page){
        $sql = "SELECT * 
          FROM users
          WHERE userPaidStartDate <= GETDATE() AND userPaidEndDate >= GETDATE()

          ORDER BY userId DESC 
          OFFSET " . ((int)$page * 10) . " ROWS FETCH NEXT 10 ROWS ONLY                      
        ";
        $params = array();

        $resP = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
            array(
                'sql' => $sql,
                'params' => $params
            )
        );
        //var_dump($resP);die;
        foreach ($resP as $i => $row) {
            $row = (array)$row;
            var_dump($row['userId']);

            $sql = "SELECT * 
              FROM payments_subscription
              WHERE userId = " . $row['userId'];
            $params = array();

            $resSub = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
                array(
                    'sql' => $sql,
                    'params' => $params
                )
            );
            if(count($resSub) > 0) {
                $payRow = (array)$resSub[0];

                $this->savePaymentToUser($payRow);
            }else{
                $userRepo = $this->em->getRepository('AppBundle:User');

                $user = $userRepo->findOneBy(array('msId' => (int)$row['userId']));
                if(!$user){
                    $user = $this->updateUserByMs(array('userId'=>(int)$row['userId']),true);
                }
                if($user) {
                    $user->setStartSubscription(new \DateTime($row['userPaidStartDate']));
                    $user->setEndSubscription(new \DateTime($row['userPaidEndDate']));
                    $this->em->persist($user);
                    $this->em->flush();
                }else{
                    var_dump($row['userId']);die;
                }
            }
        }



        /*
         * [subscriptionId]
              ,[nextPaymentDate]
              ,[paymentAmount]
              ,[creditCardNum]
              ,[creditCardCVV]
              ,[creditCardMonth]
              ,[creditCardYear]
              ,[creditCardOwnerId]
              ,[creditCardOwnerName]
              ,[groupId]
              ,[productId]
              ,[itemId]
              ,[userId]
              ,[moved]
              ,[token]
              ,[token_status]
              ,[userName]
              ,[unit]
              ,[payPeriod]
        */
        /*$sql = "SELECT *
          FROM payments_subscription
          WHERE userId > 0
          ORDER BY nextPaymentDate DESC 
          OFFSET " . ((int)$page * 10) . " ROWS FETCH NEXT 10 ROWS ONLY                      
        ";
        $params = array();

        $resP = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
            array(
                'sql' => $sql,
                'params' => $params
            )
        );

        //var_dump($resP);die;
        foreach ($resP as $i => $row) {
            $row = (array)$row;
            var_dump($row['userId']);
            $this->savePaymentToUser($row);

        }*/
        return count($resP);
    }

    public function savePaymentToUser($rowPayment){
        $userRepo = $this->em->getRepository('AppBundle:User');
        $paymentRepo = $this->em->getRepository('AppBundle:Payment');
        $paymentHistoryRepo = $this->em->getRepository('AppBundle:PaymentHistory');
        $row = (array)$rowPayment;


        $user = $userRepo->findOneBy(array('msId' => (int)$row['userId']));
        if(!$user){
            $user = $this->updateUserByMs(array('userId'=>(int)$row['userId']),true);
        }
        if($user){
            $sql = "SELECT u.*
                    FROM users u  
                    WHERE u.userId=" . (int)$row['userId'];

                $params = array();

            $u_row = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
                array(
                    'sql' => $sql,
                    'params' => $params
                )
            );
            //var_dump($u_row);die;
            $u_row = (array)$u_row[0];
            //if ($u_row['userPaidStartDate'] != null) {
                $user->setStartSubscription(new \DateTime($u_row['userPaidStartDate']));
            //}
            //userPaidEndDate
            //if ($u_row['userPaidEndDate'] != null) {
                $user->setEndSubscription(new \DateTime($u_row['userPaidEndDate']));
            //}
            $this->em->persist($user);
            $this->em->flush();


            $payment = $paymentRepo->findOneBy(array('transactionId' => trim($row['token'])));

            $params['payPeriod'] = $payPeriod = $row['payPeriod'];
            $params['recordId'] = trim($row['token']);
            $params['amount'] = (int)$row['paymentAmount'];
            $params['firstName'] = $row['userName'];
            $params['productId'] = $row['productId'];
            $params['nextPaymentDate'] = $row['nextPaymentDate'];
            $params['amount'] = $amount = $row['paymentAmount'];
            $params['phone'] = '';

            if (!$payment) {

                $payment = new Payment();
                $payment->setUser($user);
                $payment->setTransactionId(trim($row['token']));

                $payment->setAmount($amount);
                $payment->setName(urldecode($params['firstName']));
                $payment->setFullData($params);

                if ($payPeriod == '-1') {
                    $period = '2 week';
                } else {
                    $period = (int)$payPeriod . ' month' . (((int)$payPeriod == 1) ? '' : 's');
                }
                $payment->setPayPeriod($period);
                $payment->setPhone($params['phone']);

                $nextPayDate = new \DateTime($params['nextPaymentDate']);
                $payment->setNextPaymentDate($nextPayDate);
                $this->em->persist($payment);
                $this->em->flush();
            }else{
                //if(trim($row['token']) == '8388A313-5CA9-EA11-B815-ECEBB8951F7E'){
                    $nextPayDate = new \DateTime($params['nextPaymentDate']);
                    if($nextPayDate !== $payment->getNextPaymentDate()){
                        $payment->setNextPaymentDate($nextPayDate);
                        $payment->setFullData($params);
                        $this->em->persist($payment);
                        $this->em->flush();
                    }
                    //var_dump($row['nextPaymentDate'], new \DateTime($params['nextPaymentDate']) === $payment->getNextPaymentDate());die;

                //}
            }
            $payHistory = $paymentHistoryRepo->findOneBy(array('payment'=>$payment));
            if(!$payHistory) {
                $endDate = $startDate = $payment->getNextPaymentDate();

                $payHistory = new PaymentHistory();
                $payHistory->setEndPaymentDate($endDate);
                //var_dump($row['payPeriod']);die;
                if ($params['payPeriod'] == '-1') {
                    $strPer = 'P14D';
                } elseif ($params['payPeriod'] == '12') {
                    $strPer = 'P1Y';
                } else {
                    $strPer = 'P' . (int)$params['payPeriod'] . 'M';
                }

                $startDate->sub(new \DateInterval($strPer));

                $payHistory->setPaymentDate($startDate);
                $payHistory->setPayment($payment);
                $payHistory->setFullData(array());
                $this->em->persist($payHistory);
                $this->em->flush();
            }
        }else{
            //var_dump($row['userId'],$row);die;
        }
    }

    public function getImagesFromOldSite($entity){
        $sql = "SELECT *
            FROM images
            WHERE userId = ?
            ORDER BY imgId ASC";
        $params = array($entity->getMsId());

        $resP = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
            array(
                'sql' => $sql,
                'params' => $params
            )
        );
        set_time_limit(300);
        $photo = 0;
        $userPhotos = $entity->getPhotos();
        if (count($resP) > 0 and count($userPhotos) == 0) {

            //var_dump($res,count($userPhotos));dump($userPhotos);die;
            if ($entity->getId() != 3) {
                if (count($userPhotos) > 0) {
                    foreach ($userPhotos as $photo) {

                        $entity->removePhoto($photo);
                        $this->em->remove($photo);
                        $this->em->flush();
                    }

                }

                foreach ($resP as $rowPhoto) {
                    $rowPhoto = (array)$rowPhoto;
                    $isMain = ($rowPhoto['imgMain'] == '1');
                    $isValid = ($rowPhoto['imgValidated'] == '1');
                    $photo = new Photo();
                    $photo->setUser($entity);

                    $tmpFile = $_SERVER['DOCUMENT_ROOT'] . '/images/'.$entity->getId().'.jpg';
                    //var_dump(file_exists('https://www.zigzug.co.il/images/users/original/' . $rowPhoto['imgId'] . '.jpg'));die;
                    $fileOld = ($this->file_isset('https://www.zigzug.co.il/images/users/large/' . $rowPhoto['imgId'] . '.jpg')) ? 'https://www.zigzug.co.il/images/users/large/' . $rowPhoto['imgId'] . '.jpg' :
                        ($this->file_isset('https://www.zigzug.co.il/images/users/original/' . $rowPhoto['imgId'] . '.jpg') ? 'https://www.zigzug.co.il/images/users/original/' . $rowPhoto['imgId'] . '.jpg' : 'https://www.zigzug.co.il/images/users/small/' . $rowPhoto['imgId'] . '.jpg');
                    //sleep(1);
                    copy($fileOld, $tmpFile);
                    //var_dump($this->file_isset('https://www.zigzug.co.il/images/users/large/102595.jpg'));die;
                    $file = new UploadedFile($tmpFile, 'tmp.jpg', null, null, null, true);
                    $photo->setFile($file);
                    //$photo->upload();
                    $photo->setIsValid($isValid);
                    $photo->setIsMain($isMain);

                    $this->em->persist($photo);
                    $this->em->flush();


                    $entity->addPhoto($photo);
                    $this->em->persist($entity);
                    $this->em->flush();

                    //var_dump($rowPhoto, $photo->getWebPath());die;
                    $optimized = $this->applyFilterToPhoto('optimize_original', $photo->getWebPath());
                    $this->savePhoto($optimized, $photo->getAbsolutePath());

                    //die;
                    // unlink($tmpFile);
                    $photo++;
                }
            }
        }
        return $entity;
    }

    private function applyFilterToPhoto($filterName, $webPath)
    {
        $dataManager = $this->container->get('liip_imagine.data.manager');
        $image = $dataManager->find($filterName, $webPath);
        return $this->container->get('liip_imagine.filter.manager')->applyFilter($image, $filterName)->getContent();
    }

    public function savePhoto($photo, $path)
    {
        $f = fopen($path, 'w');
        fwrite($f, $photo);
        fclose($f);
    }

    public function uploadUserMessage($entety, $page = 1){
        set_time_limit(1000);
        $countPerPage = ($this->quick) ? 100 : 2;

        $sql = "EXEC app_user_contacts_paged " . $entety->getMsId() . ",".$countPerPage."," . $page;

        $params = array();

        $inbox = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
            array(
                'sql' => $sql,
                'params' => $params
            )
        );
        if($entety->getId() == 3) {
            //var_dump($inbox);die;
        }
        $connection = $this->em->getConnection();
        $userRepo = $this->em->getRepository('AppBundle:User');

        foreach ($inbox as $i => $chat) {
            $chat = (array)$chat;
            $fromUser = $chat['msgFromId'] == $entety->getMsId() ? $entety : $userRepo->findOneBy(array('msId' => $chat['msgFromId']));
            $toUser = $chat['msgToId'] == $entety->getMsId() ? $entety : $userRepo->findOneBy(array('msId' => $chat['msgToId']));
            if(!$fromUser){
                $fromUser = $this->updateUserByMs(array('userId'=>$chat['msgFromId']));// uploadUserByMsId($chat['msgFromId']);
            }
            if(!$toUser){
                $toUser = $this->updateUserByMs(array('userId'=>$chat['msgToId']));// $this->uploadUserByMsId($chat['msgToId']);
            }
            if($fromUser and $toUser){
                $stmt = $connection->prepare("
                    SELECT id FROM messengerLastMessages
                    WHERE ((user1 = '" . $fromUser->getId() . "' and user2 = '" . $toUser->getId() . "') 
                    OR (user2 = '" . $fromUser->getId() . "' and user1 = '" . $toUser->getId() . "')) and ms_upload = 1");
                $stmt->execute();
                $check = $stmt->fetchAll();
                if(count($check) == 0 ) {
                    $stmt = $connection->prepare("
                        DELETE FROM messengerLastMessages
                        WHERE (user1 = '" . $fromUser->getId() . "' and user2 = '" . $toUser->getId() . "') 
                        OR (user2 = '" . $fromUser->getId() . "' and user1 = '" . $toUser->getId() . "')");
                    $stmt->execute();
                    $stmt = $connection->prepare("
                        DELETE FROM messenger
                        WHERE (fromUser = '" . $fromUser->getId() . "' and toUser = '" . $toUser->getId() . "') OR (toUser = '" . $fromUser->getId() . "' and fromUser = '" . $toUser->getId() . "')");
                    $stmt->execute();

                    $sql = "SELECT TOP 60
                            msgId,msgBody,msgFromId,msgToId,msgRead,msgDate,msgToDel,msgFromDel,audio
                        FROM
                            messages
                        WHERE
                            ((msgToId = " . $toUser->getMsId() . " AND msgFromId = " . $fromUser->getMsId() . ")
                            OR
                            (msgToId = " . $fromUser->getMsId() . " AND msgFromId = " . $toUser->getMsId() . " ))
                            AND (msgToDel = 0 OR msgFromDel = 0)
                            AND msgDate > '2019-01-01 00:00:00'
                        ORDER BY msgDate DESC";
                    $params = array();

                    $messages = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
                        array(
                            'sql' => $sql,
                            'params' => $params
                        )
                    );
                    $lastMessFrom = $lastMessTo = null;
                    foreach ($messages as $mess) {
                        $row = (array)$mess;
                        $splitArr = explode("========= הודעה מקורית =========", $row['msgBody']);
                        $row['msgBody'] = iconv(mb_detect_encoding($splitArr[0], mb_detect_order(), true), "UTF-8", $splitArr[0]);
                        //mb_convert_encoding($splitArr[0],'UTF-8');
                        //insert messages
                        $fromId = ($row['msgFromId'] == $fromUser->getMsId()) ? $fromUser->getId() : $toUser->getId();
                        $toId = ($row['msgToId'] == $fromUser->getMsId()) ? $fromUser->getId() : $toUser->getId();
                        //var_dump($row['msgBody']);die;
                        $stmt = $connection->prepare("INSERT INTO messenger (`fromUser`, `toUser`, `date`, `isRead`, `isFromDel`, `isToDel`, `isDelivered`, `isNotified`, `msgFromDel`, `msgToDel`, `message`) 
                            VALUES (" . $fromId . ", " . $toId . ", ?, " . $row['msgRead'] . ", " . $row['msgFromDel'] . ", " . $row['msgToDel'] . ", 1, 1, " . $row['msgFromDel'] . ", " . $row['msgToDel'] . ", ?)");
                        //$stmt->bindValue(1, $row['msgBody']);
                        $stmt->bindValue(1, $row['msgDate']);
                        $stmt->bindValue(2, $row['msgBody']);
                        $stmt->execute();
                        $messageId = $connection->lastInsertId();
                        $row['messageId'] = $messageId;
                        if (!$lastMessFrom and (($row['msgFromId'] == $fromUser->getMsId() and $row['msgFromDel'] == '0') or ($row['msgToId'] == $fromUser->getMsId() and $row['msgToDel'] == '0'))) {
                            $lastMessFrom = $row;
                        }
                        if (!$lastMessTo and (($row['msgFromId'] == $toUser->getMsId() and $row['msgFromDel'] == '0') or ($row['msgToId'] == $toUser->getMsId() and $row['msgToDel'] == '0'))) {
                            $lastMessTo = $row;
                        }
                    }

                    $stmt = $connection->prepare("INSERT INTO messengerLastMessages (messageid, messageid2, user1, user2, message, `date`, message2, date2, user1_del, user2_del, ms_upload)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
                    $mes1id = $lastMessFrom ? $lastMessFrom['messageId'] : null;
                    $mes2id = $lastMessTo ? $lastMessTo['messageId'] : null;
                    $mes1 = $lastMessFrom ? $lastMessFrom['msgBody'] : null;
                    $mes2 = $lastMessTo ? $lastMessTo['msgBody'] : null;
                    $date = $lastMessFrom ? $lastMessFrom['msgDate'] : null;
                    $date2 = $lastMessTo ? $lastMessTo['msgDate'] : null;
                    $user1Del = $lastMessFrom ? 0 : 1;
                    $user2Del = $lastMessTo ? 0 : 1;
                    $stmt->bindValue(1, $mes1id);
                    $stmt->bindValue(2, $mes2id);
                    $stmt->bindValue(3, $fromUser->getId());
                    $stmt->bindValue(4, $toUser->getId());
                    $stmt->bindValue(5, $mes1);
                    $stmt->bindValue(6, $date);
                    $stmt->bindValue(7, $mes2);
                    $stmt->bindValue(8, $date2);
                    $stmt->bindValue(9, $user1Del);
                    $stmt->bindValue(10, $user2Del);
                    $stmt->execute();
                    //var_dump($connection->lastInsertId(),$lastMessFrom,$lastMessTo);die;


                    //black_list, view, favorite of inbox
                    /*
                    $stmt = $connection->prepare("
                    DELETE FROM view
                    WHERE (member_id = '" . $fromUser->getId() . "' and owner_id = '" . $toUser->getId() . "') OR (member_id = '" . $toUser->getId() . "' and owner_id = '" . $fromUser->getId() . "')");
                    $stmt->execute();

                    $sql = "SELECT *
                            FROM
                                lists_lookedAtMe
                            WHERE
                                (listOwnerId = " . $toUser->getMsId() . " AND listMemberId = " . $fromUser->getMsId() . ")
                                OR
                                (listOwnerId = " . $fromUser->getMsId() . " AND listMemberId = " . $toUser->getMsId() . " )";
                    $params = array();

                    $views = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
                        array(
                            'sql' => $sql,
                            'params' => $params
                        )
                    );

                    foreach ($views as $view) {
                        //var_dump($views);die();
                        $memberId = $toUser->getMsId() == $view->listMemberId ? $toUser->getId() : $fromUser->getId();
                        $ownerId = $toUser->getMsId() == $view->listOwnerId ? $toUser->getId() : $fromUser->getId();
                        $stmt = $connection->prepare("INSERT INTO view (member_id, owner_id) VALUES ($memberId, $ownerId)");
                        $stmt->execute();
                    }

                    $stmt = $connection->prepare("
                    DELETE FROM favorite
                    WHERE (member_id = '" . $fromUser->getId() . "' and owner_id = '" . $toUser->getId() . "') OR (member_id = '" . $toUser->getId() . "' and owner_id = '" . $fromUser->getId() . "')");
                    $stmt->execute();

                    $sql = "SELECT *
                            FROM
                                lists_favorite
                            WHERE
                                (listOwnerId = " . $toUser->getMsId() . " AND listMemberId = " . $fromUser->getMsId() . ")
                                OR
                                (listOwnerId = " . $fromUser->getMsId() . " AND listMemberId = " . $toUser->getMsId() . " )";
                    $params = array();

                    $favs = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
                        array(
                            'sql' => $sql,
                            'params' => $params
                        )
                    );
                    foreach ($favs as $fav) {
                        $memberId = $toUser->getMsId() == $fav->listMemberId ? $toUser->getId() : $fromUser->getId();
                        $ownerId = $toUser->getMsId() == $fav->listOwnerId ? $toUser->getId() : $fromUser->getId();
                        $stmt = $connection->prepare("INSERT INTO favorite (member_id, owner_id) VALUES ($memberId, $ownerId)");
                        $stmt->execute();
                    }

                    $stmt = $connection->prepare("
                    DELETE FROM black_list
                    WHERE (member_id = '" . $fromUser->getId() . "' and owner_id = '" . $toUser->getId() . "') OR (member_id = '" . $toUser->getId() . "' and owner_id = '" . $fromUser->getId() . "')");
                    $stmt->execute();

                    $sql = "SELECT *
                            FROM
                                lists_black
                            WHERE
                                (listOwnerId = " . $toUser->getMsId() . " AND listMemberId = " . $fromUser->getMsId() . ")
                                OR
                                (listOwnerId = " . $fromUser->getMsId() . " AND listMemberId = " . $toUser->getMsId() . " )";
                    $params = array();

                    $blacks = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
                        array(
                            'sql' => $sql,
                            'params' => $params
                        )
                    );
                    foreach ($blacks as $black) {
                        $memberId = $toUser->getMsId() == $black->listMemberId ? $toUser->getId() : $fromUser->getId();
                        $ownerId = $toUser->getMsId() == $black->listOwnerId ? $toUser->getId() : $fromUser->getId();
                        $stmt = $connection->prepare("INSERT INTO black_list (member_id, owner_id) VALUES ($memberId, $ownerId)");
                        $stmt->execute();
                    }
                    */
                }
            }
        }
        //$countSuccess = (($page - 1) * 100) + count($inbox);
        //var_dump($countSuccess);
        // var_dump(count($inbox));die;
        return (count($inbox) < $countPerPage) ? 'done' : (int)$page + 1;
        // return (count($inbox) < 100) ? true : $this->uploadUserMessage($entety, (int)$page + 1);
    }

    public function setListUser($entity, $data){
        $connection = $this->em->getConnection();
        $userRepo = $this->em->getRepository('AppBundle:User');
        $countPerPage = ($this->quick) ? 100 : 40;
        set_time_limit(1000);
        $sql = "SELECT *
                            FROM
                                " . $data['ms_table'] . "
                            WHERE
                                (listOwnerId = " . $entity->getMsId() . ")
                                OR
                                (listMemberId = " . $entity->getMsId() . ")" .
                                " ORDER BY listOwnerId ASC 
                                OFFSET " . ((int)$data['page'] * $countPerPage) . " ROWS FETCH NEXT ".$countPerPage." ROWS ONLY";


        $params = array();

        $blacks = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
            array(
                'sql' => $sql,
                'params' => $params
            )
        );

        foreach ($blacks as $i => $black) {
            $fromUser = $userRepo->findOneBy(array('msId' => ($entity->getMsId() == $black->listMemberId ? $black->listOwnerId : $black->listMemberId)));
            if(!$fromUser){
                $fromUser = $this->updateUserByMs(array('userId' => ($entity->getMsId() == $black->listMemberId ? $black->listOwnerId : $black->listMemberId)));
            }
            if($fromUser) {
                $memberId = $entity->getMsId() == $black->listMemberId ? $entity->getId() : $fromUser->getId();
                $ownerId = $entity->getMsId() == $black->listOwnerId ? $entity->getId() : $fromUser->getId();
                $stmt = $connection->prepare("SELECT * FROM " . $data['table'] . " WHERE member_id = $memberId AND owner_id = $ownerId");
                $stmt->execute();
                if (!$stmt->fetch()) {
                    $stmt = $connection->prepare("INSERT INTO " . $data['table'] . " (member_id, owner_id) VALUES ($memberId, $ownerId)");
                    $stmt->execute();
                }
            }
        }
        return (count($blacks) < $countPerPage) ? 'done' : ((int)$data['page'] + 1);
    }

    public function uploadUserLists($entity){

        if($this->transferStatus and $this->transferStatus->lists !== 'done'){
            $data = explode('_', $this->transferStatus->lists);
            $list = $data[0];
            $page = (int)$data[1];

            if($list == 'black'){
                $page = $this->setListUser($entity, array(
                    'ms_table' => 'lists_black',
                    'table' => 'black_list',
                    'page' => $page
                ));

                if($page == 'done'){
                    $list = 'fav';
                    $page = 0;
                }
            }elseif ($list == 'fav') {
                $page = $this->setListUser($entity, array(
                    'ms_table' => 'lists_favorite',
                    'table' => 'favorite',
                    'page' => $page
                ));
                if ($page == 'done') {
                    $list = 'contact';
                    $page = 0;
                }
            }elseif ($list == 'contact'){
                $page = $this->setListUser($entity, array(
                    'ms_table' => 'lists_contactedMe',
                    'table' => 'contact',
                    'page' => $page
                ));
                if ($page == 'done') {
                    $list = 'view';
                    $page = 0;
                }
            }elseif ($list == 'view'){
                $page = $this->setListUser($entity, array(
                    'ms_table' => 'lists_lookedAtMe',
                    'table' => 'view',
                    'page' => $page
                ));
            }
            return ($page === 'done') ? 'done' : ($list . '_' . $page);
        }
    }

    public function uploadUserLikes($entety, $page = 1){
        if((int)$page == 0){
            return 1;
        }
        $connection = $this->em->getConnection();
        $lPage = (int)$page - 1;
        //likes
        $countPerPage = ($this->quick) ? 100 : 40;
        set_time_limit(1000);
        $sql = "SELECT *
                FROM likeMe
                WHERE listMemberId = " . $entety->getMsId() . " OR listOwnerId = " . $entety->getMsId() . "
                ORDER BY id ASC
                OFFSET " . ((int)$lPage * $countPerPage) . " ROWS FETCH NEXT " . $countPerPage . " ROWS ONLY";//OFFSET 11000 ROWS FETCH NEXT 2000 ROWS ONLY
        $params = array();

        $likes = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
            array(
                'sql' => $sql,
                'params' => $params
            )
        );


        foreach ($likes as $like){
            $like = (array)$like;

            $userFrom = $this->em->getRepository('AppBundle:User')->findOneBy(array('msId' => $like['listOwnerId']));
            $userFrom = (!$userFrom) ? $this->updateUserByMs(array('userId'=>$like['listOwnerId'])) : $userFrom;
            $userTo = $this->em->getRepository('AppBundle:User')->findOneBy(array('msId' => $like['listMemberId']));
            $userTo = (!$userTo) ? $this->updateUserByMs(array('userId'=>$like['listMemberId'])) : $userTo;
            /* or ($userTo and (int)$userTo->getMsUpload() == 0) */
            if($userFrom and $userTo) {
                $stmt = $connection->prepare("
                    SELECT id FROM like_me
                    WHERE (from_id = '" . $userFrom->getId() . "' and to_id = '" . $userTo->getId() . "') 
                    OR (to_id = '" . $userFrom->getId() . "' and from_id = '" . $userTo->getId() . "')");
                $stmt->execute();
                $check = $stmt->fetchAll();
                if (count($check) == 0) {
                    if($userFrom->getId() == 3){
                        $like['ownerSplashScreen'] = 1;
                    }
                    if($userTo->getId() == 3){
                        $like['memberSplashScreen'] = 1;
                    }
                    $stmt = $connection->prepare("INSERT INTO like_me (from_id, to_id, is_bingo, is_show_splash_from, is_show_splash_to) VALUES (" . $userFrom->getId() . ", " . $userTo->getId() . ", " . $like['bingo'] . ", " . $like['ownerSplashScreen'] . ", " . $like['memberSplashScreen'] . ")");
                    $stmt->execute();
                    $likeId = $connection->lastInsertId();
                    //var_dump($likeId);
                    $sql = "SELECT * FROM userNotifications	WHERE likeMeId = " . $like['id'];
                    $notifications = $this->callAPI('POST', 'https://m.zigzug.co.il/api/v2/db/',
                        array(
                            'sql' => $sql,
                            'params' => $params
                        )
                    );
                    foreach ($notifications as $notif) {
                        $notif = (array)$notif;
                        $userId = ($notif['userId'] == $userFrom->getMsId()) ? $userFrom->getId() : $userTo->getId();
                        //notificationId,date,isRead
                        $stmt = $connection->prepare("INSERT INTO user_notifications (notifications_id, user_id, like_me_id, `date`, is_read) VALUES (" . $notif['notificationId'] . ", " . $userId . ", " . $likeId . ", \"" . $notif['date'] . "\", " . $notif['isRead'] . ")");
                        $stmt->execute();
                    }
                }
            }

        }
        return (count($likes) < $countPerPage) ? 'done' : (int)$page + 1;
    }

    public function setUser($user){
        $this->user = $user;
        $this->getUserTransferStatus();
    }

    public function getUser($user){
        return $this->user;
    }

    public function getUserTransferStatus(){
        $msId = (int)$this->user->getMsId();
        if($this->user and $msId > 0) {
            $connection = $this->em->getConnection();
            $stmt = $connection->prepare("SELECT * FROM data_transfer WHERE ms_id=" . $this->user->getMsId());
            $stmt->execute();
            $data = $stmt->fetch();
            //var_dump($data);die;
            if ($data) {
                $this->transferStatus = (object)$data;
            } else {

                $this->transferStatus = (object)array(
                    'user_id' => $this->user->getId(),
                    'ms_id' => $this->user->getMsId(),
                    'username' => $this->user->getUsername(),
                    'email' => $this->user->getEmail(),
                    'messages' => 0,
                    'likes' => 0,
                    'lists' => 'black_0',
                    'id' => 0
                );
            }
        }

    }

    public function getTransferStatus(){
        return $this->transferStatus;
    }

    public function getOtherUserId(){
        $connection = $this->em->getConnection();
        $id = 3;

        if((int)$this->transferStatus->other > 0){

            $stmt = $connection->prepare("SELECT lists FROM data_transfer WHERE user_id = " . (int)$this->transferStatus->other);
            $stmt->execute();
            $uD = $stmt->fetch();
            if(!$uD or ($uD and $uD['lists'] !== 'done')){
                return $this->transferStatus->other;
            }else{
                $id = $this->transferStatus->other;
            }
        }
        $stmt = $connection->prepare("SELECT id FROM user WHERE id > " . $id);
        $stmt->execute();
        $res = $stmt->fetch();
        //
        return $res['id'];
    }

    public function setOtherUser(){
        $connection = $this->em->getConnection();
        //var_dump($this->admin);die;
        if($this->admin){
            $stmt = $connection->prepare("UPDATE data_transfer SET other=". (int)$this->transferStatus->user_id . " WHERE user_id = 3");
            $stmt->execute();
        }
    }

    public function getOtherUser(){
        $res = null;
        if($this->user->getId() == 3 and $this->transferStatus->lists == 'done'){
            $this->admin = true;

            $user = $this->em->getRepository('AppBundle:User')->findOneBy(array('id' => $this->getOtherUserId()));
            $this->setUser($user);
            $this->setOtherUser();
            $res = $this->updateData();
            $res = 'update';
        }
        return $res;
    }

    public function updateUserTransferStatus(){
        if($this->transferStatus) {
            $connection = $this->em->getConnection();
            $messages = $this->transferStatus->messages;
            $likes = $this->transferStatus->likes;
            $user_id = $this->transferStatus->user_id;
            $ms_id = $this->transferStatus->ms_id;
            $username = $this->transferStatus->username;
            $email = $this->transferStatus->email;
            $lists = $this->transferStatus->lists;
            // var_dump($this->transferStatus);die;

            if(isset($this->transferStatus->id) and (int)$this->transferStatus->id > 0){
                $sql = "UPDATE data_transfer SET user_id=?, ms_id=?, username=?, email=?, messages=?, likes=?, lists=? WHERE id='" . $this->transferStatus->id . "'";
            }else {
                // $this->updateUserByMs(array('userId' => $ms_id), true);
                $sql = "INSERT INTO  data_transfer (user_id, ms_id, username, email, messages, likes, lists) VALUES (?,?,?,?,?,?,?)";
            }
            //var_dump($sql);die;
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $user_id);
            $stmt->bindValue(2, $ms_id);
            $stmt->bindValue(3, $username);
            $stmt->bindValue(4, $email);
            $stmt->bindValue(5, $messages);
            $stmt->bindValue(6, $likes);
            $stmt->bindValue(7, $lists);
            $stmt->execute();
        }
    }

    public function file_isset($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($code == 200) {
            $status = true;
        } else {
            $status = false;
        }
        curl_close($ch);
        return $status;
    }
}
