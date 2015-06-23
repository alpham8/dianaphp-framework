<?php
/**
 * AuthList.php
 *
 * The "true" ACL class for checking if an action is allowed to access or not
 *
 * @package De.Twunner.Authentification
 * @version v0.0.1
 * @author Thomas Wunner <th.wunner@gmx.de>
 * @copyright CC by SA Copyrith (c) 2014, Thomas Wunner
 *
 *
 * @since API v0.0.1
 */
class AuthList
{
    const USER_ALREADY_SET = -1;
    const USER_GROUP_CHANGED = 0;
    const USER_SUCCESSFULLY_ADDED = 1;

    const ACTION_ALREADY_ALLOWED = -1;
    const ACTION_SUCCESSFULLY_ALLOWED = 0;

    const ACTION_ALREADY_DENIED = -1;
    const ACTION_SUCESSFULLY_DENIED = 0;

    const GROUP_ALREADY_EXISTS = -1;
    const GROUP_SUCCESSFULLY_ADDED = 0;

    const ACTION_ACCEPT = 'OK';
    const ACTION_DENY = 'NOK';

    const LIST_KEY_NAME = 'list';

    protected $_rights;
    protected $_list;
    protected $_groups;

    public function __construct()
    {
        $this->_list = array();
        $this->_groups = array();
    }

    public function addAction ($sAction)
    {
        if (is_callable($sAction))
        {
            $this->_list[] = $sAction->__toString();
        }
        else
        {
            $this->_list[] = $sAction;
        }
    }

    public function addGroup ($sGroupName, $rights = array())
    {
        $iRetCode = -2;
        if (in_array($sGroupName, $this->_groups))
        {
            $iRetCode = self::GROUP_ALREADY_EXISTS;
        }
        else
        {
            if (empty($rights))
            {
                $this->_groups[$sGroupName] = array();
            }
            else
            {

                $this->_groups[$sGroupName][self::LIST_KEY_NAME] = $rights;
            }
            $iRetCode = self::GROUP_SUCCESSFULLY_ADDED;
        }

        return $iRetCode;
    }

    public function setPrimaryUserGroup ($sGroupName, $sUser)
    {
        $iRetCode = -2;

        foreach ($this->_groups as $sKeyGroupName => $users)
        {
            if (in_array($sUser, $users) && $sGroupName === $sKeyGroupName)
            {
                // User already are in the requested group, finish working
                $iRetCode = self::USER_ALREADY_SET;
                break;
            }

            elseif (in_array($sUser, $users) && $sGroupName !== $sKeyGroupName)
            {
                for ($iUserPos = 0; $iUserPos < count($users); $iUserPos++)
                {
                    if ($users[$iUserPos] === $sUser)
                    {
                        $iUserNo = $iUserPos;
                        break;
                    }
                }
                unset($users[$iUserNo]);
                $this->_groups[$sGroupName][] = $sUser;
                $iRetCode = self::USER_GROUP_CHANGED;
                break;
            }

            else
            {
                $this->_groups[$sGroupName][] = $sUser;
                $iRetCode = self::USER_SUCCESSFULLY_ADDED;
                break;
            }
        }

        return $iRetCode;
    }

    public function allowAction ($sGroupName, $sActionName)
    {
        $iRetCode = -2;
        if ($this->_groups[$sGroupName][self::LIST_KEY_NAME][$sActionName] === self::ACTION_ACCEPT)
        {
            $iRetCode = self::ACTION_ALREADY_ALLOWED;
        }
        else
        {
            $this->_groups[$sGroupName][self::LIST_KEY_NAME][$sActionName] = self::ACTION_ACCEPT;
            $iRetCode = self::ACTION_SUCCESSFULLY_ALLOWED;
        }

        return $iRetCode;
    }

    public function denyAction ($sGroupName, $sActionName)
    {
        $iRetCode = -2;
        if ($this->_groups[$sGroupName][self::LIST_KEY_NAME][$sActionName] === self::ACTION_DENY)
        {
            $iRetCode = self::ACTION_ALREADY_DENIED;
        }
        else
        {
            $this->_groups[$sGroupName][self::LIST_KEY_NAME][$sActionName] = self::ACTION_DENY;
            $iRetCode = self::ACTION_SUCESSFULLY_DENIED;
        }

        return $iRetCode;
    }

    public function isAllowed ($sUserName, $sActionName)
    {
        $rwRegular = false;
        $rwBefore = false;

        //$i = 0;
        //foreach ($this->_groups as $users)
        //{
        //    if (in_array($sUserName, $users))
        //    {
        //        if (array_key_exists($sActionName, $users[self::LIST_KEY_NAME]))
        //        {
        //            $rw = $users[self::LIST_KEY_NAME][$sActionName] === self::ACTION_ACCEPT;
        //        }
        //
        //        else
        //        {
        //            $j = 0;
        //            foreach ($this->_groups as $beforeUsers)
        //            {
        //                if (array_key_exists($sActionName, $beforeUsers[self::LIST_KEY_NAME]))
        //                {
        //                    $rw = $beforeUsers[self::LIST_KEY_NAME][$sActionName] === self::ACTION_ACCEPT;
        //                }
        //
        //                if ($j === $i)
        //                {
        //                    break;
        //                }
        //                $j++;
        //            }
        //        }
        //
        //        break;
        //    }
        //    $i++;
        //}

        $i = 0;
        $iFoundGroup = 0;
        $iFoundActionBefore = 0;
        foreach ($this->_groups as $users)
        {
            if (in_array($sUserName, $users))
            {
                $iFoundGroup = $i;
                if (array_key_exists($sActionName, $users[self::LIST_KEY_NAME]))
                {
                    $rwRegular = $users[self::LIST_KEY_NAME][$sActionName] === self::ACTION_ACCEPT;
                }
                break;
            }

            else
            {
                if (array_key_exists($sActionName, $users[self::LIST_KEY_NAME]))
                {
                    $iFoundActionBefore = $i;
                    $rwBefore = $users[self::LIST_KEY_NAME][$sActionName] === self::ACTION_ACCEPT;
                }
            }
            $i++;
        }

        if ($rwRegular)
        {
            return true;
        }

        return $iFoundActionBefore < $iFoundGroup && $rwBefore;
    }

    public function dump()
    {
        var_dump($this->_groups);
    }
}
?>
