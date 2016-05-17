<?php
namespace OCA\SingleSignOn;

class RedirectRegion implements IRedirectRegion{
    public static function getRegionUrl($region) {
        $params = array();

        $config = \OC::$server->getSystemConfig();
        $regions = $config->getValue("sso_regions");

        $regionNum = $regions[$region] == "north" ? "1" : "2";

        $params["srv"] = $regionNum;
        $params["path"] = "index.php";

        $url = $config->getValue("sso_login_url") . "?" . http_build_query($params);
        return $url;
    }
}
