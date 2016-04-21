<?php
namespace OCA\SingleSignOn;

class GetInfo implements IUserInfoRequest {
    private $connection;
    private $userId;
    private $email;
    private $groups = array();
    private $userGroup;
    private $displayName;
    private $errorMsg;
    private $sid;

    public function __construct($connection){
        $this->connection = $connection;
    }

    public function name() {
        return ISingleSignOnRequest::INFO;
    }

    public function send($data = null) {
        $serverConnection = $this->connection->getConnection();
        $serverUrl = $this->connection->getServerUrl();
        $param = array("cmd" => "check_key", 
                       "userid" => $data["userid"],
                       "key" => $data["key"]);

        $url = $serverUrl . "?" . http_build_query($param);

        curl_setopt($serverConnection, CURLOPT_URL, $url);
        $result = curl_exec($serverConnection);
        $result = json_decode($result, true);

        if ($result["retcode"] != 0) {
            return false;
        }

        $userInfo = $result["user_info"];

        $this->userId = $data["userid"];
        $this->email = $data["userid"];
        $this->displayName = $userInfo["name"];
        $this->sid = $userInfo["sid"];
        $this->openID = $userInfo["openid"];
        //$this->userGroup = $info->UserGroup;

        return true;
    }

    public function getErrorMsg() {
        return $this->errorMsg;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getGroups() {
        return $this->groups;
    }

    public function getDisplayName() {
        return $this->displayName;
    }


    /**
     * Getter for user region
     *
     * @return string user region
     */
    public function getRegion() {
        return (int)substr($this->sid,0,2);
    }

    /**
     * Check user have permassion to use the service or not
     *
     * @return bool
     */
    public function hasPermission(){
        if ($this->userGroup == "T" || $this->userGroup == "S") {
            return true;
        }

        return true;
    }
}
