<?php

namespace OCA\SingleSignOn;

/**
 * Class UserInfoSetter
 * @author Dauba
 */
class UserInfoSetter
{
    /**
     * Set ownCloud user info
     *
     * @return void
     */
    public static function setInfo($user, $userInfo)
    {
        $userID = $userInfo->getUserId();
        $advanceGroup = \OC::$server->getSystemConfig()->getValue("sso_advance_user_group", NULL);

        \OC_User::setDisplayName($userID, $userInfo->getDisplayName());
        \OC::$server->getConfig()->setUserValue($userID, "settings", "email", $userInfo->getEmail());

        if ($userInfo->getRole() === $advanceGroup) {
            \OC::$server->getConfig()->setUserValue($userID, "settings", "role", $userInfo->getRole());
            $group = \OC::$server->getGroupManager()->get($advanceGroup);
            if(!$group) {
                $group = \OC::$server->getGroupManager()->createGroup($advanceGroup);
            }
            $group->addUser($user);
        }
    }
    
}
