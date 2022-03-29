<?php

namespace App\Services\Messenger;

use mysql_xdevapi\Exception;

class AndroidDevice extends DeviceEntity
{

    public function __construct()
    {
        parent::__construct();
    }

    public function deviceExists()
    {
        $sql = "SELECT user_id, gcmdeviceid FROM userdevices WHERE user_id = ? AND gcmdeviceid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->userId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $this->id);
        $stmt->execute();

        return count($stmt->fetchAll()) > 0 ? true : false;
    }

    public function save()
    {
        if ($this->deviceExists()) {
            //$this->createStatus("success",$this->entity->upload->success->code, $this->entity->upload->success->message);
            return false;
        }
        $userAttributes = new UserAttributes();
        $userAttributes->post($this->config->userDevices, array($this->userId, $this->id, null));
        //$this->createStatus("success",$this->entity->upload->success->code, $this->entity->upload->success->message);
        return true;
    }

    public function getSimilars()
    {
        $similars = array();
        $sql = "SELECT user_id, gcmdeviceid FROM userdevices WHERE user_id=:userId AND gcmdeviceid IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("userId", $this->userId, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $similars[] = $row['gcmdeviceid'];
        }

        return $similars;
    }

    public function pushNotification($message)
    {
        try {


            // API access key from Google API's Console
            if (!defined('API_ACCESS_KEY')) {
                define('API_ACCESS_KEY', $this->config->gcm->apiKey);
            }
            $registrationIds = array($this->id);
            // prep the bundle
            $msg = array
            (
                'title' => $this->config->gcm->title,
                'message' => $message,
                //'subtitle'	=> 'This is a subtitle. subtitle',
                //'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
                'vibrate' => 1,
                'sound' => 1,
                'notId' => time(),
                //'style' 	=> 'inbox',
                //'largeIcon'	=> 'large_icon',
                //'smallIcon'	=> 'small_icon'
            );
            $fields = array
            (
                'registration_ids' => $registrationIds,
                'data' => $msg
            );

            $headers = array
            (
                'Authorization: key=' . API_ACCESS_KEY,
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->config->gcm->url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        }catch (Exception $e){
            var_dump($e);
            return $e;
    }
		
	}

}

