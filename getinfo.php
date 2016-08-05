<?php
namespace OCA\SingleSignOn;

class GetInfo implements IUserInfoRequest {
    public static $teacherRole = array("校長",
                                       "教師",
                                       "職員",
                                       "縣市管理者",
                                       "學校管理者");
    private $connection;
    private $setupParams = array();
    private $userId;
    private $email;
    private $groups = array();
    private $userGroup;
    private $displayName;
    private $errorMsg;
    private $sid;
    private $title = array();

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
        $statusCode = (int)$result["retcode"];

        if ($statusCode != 0) {
            if($this->setupParams["action"] == "webDavLogin") {
                switch ($statusCode) {
                    case 1:
                        $errorMsg = "Missing parameter 'password'";
                        break;
                    case 2:
                        $errorMsg = "Missing parameter 'userid'";
                        break;
                    case 3:
                        $errorMsg = "Userid not exsit";
                        break;
                    case 4:
                        $errorMsg = "Verification failed";
                        break;
                }
            }
            else {
                switch ($statusCode) {
                    case 1:
                        $errorMsg = "Missing parameter 'key'";
                        break;
                    case 2:
                        $errorMsg = "Error format of parameter 'key'";
                        break;
                    case 3:
                        $errorMsg = "Missing parameter 'userid'";
                        break;
                    case 4:
                        $errorMsg = "Userid not exsit";
                        break;
                    case 5:
                        $errorMsg = "Verification failed";
                        break;
                }
            }
            $this->errorMsg = $errorMsg;
            return false;
        }

        $userInfo = $result["user_info"];

        $matches = array();
        preg_match("/.*@.*/", $data["userid"], $matches);

        $this->userId = count($matches) ? $data["userid"] : $data["userid"] . "@mail.edu.tw";
        $this->email = $data["userid"];
        $this->displayName = $userInfo["name"];
        $this->sid = $userInfo["sid"];
        $this->openID = $userInfo["openid"];
        $this->token = $this->setupParams["action"] === "webDavLogin" ? $data["password"] : $data["key"];
        $titleStr = json_decode($userInfo["titleStr"])[0];
        foreach ($titleStr as $item) {
            foreach ($item->title as $title) {
                $this->title[] = $title;
            }
        }

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

    /**
     * Check has error massage or not
     *
     * @return true|false
     */
    public function hasErrorMsg()
    {
        return $this->errorMsg ? true : false;
    }
    
    /**
     * Get user role in this system
     *
     * @return string
     */
    public function getRole()
    {
        foreach ($this->title as $title) {
            if (in_array($title, self::$teacherRole)) {
                return \OC::$server->getSystemConfig()->getValue("sso_advance_user_group", NUll);
            }
        }
        return "stutent";
    }
    
}
