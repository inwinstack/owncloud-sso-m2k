<?php
namespace OCA\SingleSignOn;

class GetInfo implements IUserInfoRequest {
    private $connection;
    private $setupParams = array();
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

    /**
     * setup userinfo
     *
     * @param array $param
     * @return void
     */
    public function setup($params)
    {
        foreach ($params as $key => $value) {
            $this->setupParams[$key] = $value;
        }
    }

    public function send($data = null) {
        $serverConnection = $this->connection->getConnection();
        $serverUrl = $this->connection->getServerUrl();

        $params["userid"] = $data["userid"];

        if ($this->setupParams["action"] == "webDavLogin") {
            $params["cmd"] = "check_pwd";
            $params["passwd"] = $data["password"];
        }
        else {
            $params["cmd"] = "check_key";
            $params["key"] = $data["key"];
        }

        $url = $serverUrl . "?" . http_build_query($params);

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
        $this->token = $this->setupParams["action"] === "webDavLogin" ? $data["password"] : $data["key"];
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
     * Get user auth token
     *
     * @return string $token
     */
    public function getToken()
    {
        return $this->token;
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
