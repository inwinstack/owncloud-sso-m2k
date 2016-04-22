<?php

namespace OCA\SingleSignOn;

/**
 * Class WebDavAuthInfo
 * @author Dauba
 */
class WebDavAuthInfo implements IWebDavAuthInfo
{
    /**
     * undocumented function
     *
     * @return void
     */
    public static function init($userID, $password)
    {
        
    }
    
    /**
     * Getter for Info
     *
     * @return array
     */
    public static function get()
    {
        foreach (AuthInfo::$requireKeys as $key) {
            if(!array_key_exists($key, self::$info)) {
                return null;
            }
        }
        return self::$info;
    }
    
}
