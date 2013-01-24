<?php
 /** 
  *------------------------------------------------------------------------
  * T3 Framework for Joomla!
  * ------------------------------------------------------------------------
  * Copyright (C) 2004-2013 JoomlArt.com, Ltd. All Rights Reserved.
  * License - GNU/GPL, http://www.gnu.org/licenses/gpl.html
  * Authors:  JoomlArt, JoomlaBamboo 
  * If you want to be come co-authors of this project, please follow our guidelines at http://t3-framework.org/contribute
  * ------------------------------------------------------------------------
  */

// No direct access
defined('_JEXEC') or die();

class ObjectExtendable extends JObject
{
    var $_extendableObjects = array();

    function _extend($oObject)
    {
        $this->_extendableObjects = $oObject;
    }

    function __get($sName, &$sValue)
    {
        for ($i = 0; $i < count($this->_extendableObjects); $i++) {
            if (property_exists($this->_extendableObjects[$i], $sName)) {
                $sValue = $this->_extendableObjects[$i]->$sName;
                return true;
            }
        }

        return false;
    }

    function __set($sName, &$sValue)
    {
        for ($i = 0; $i < count($this->_extendableObjects); $i++) {
            if (property_exists($this->_extendableObjects[$i], $sName)) {
                $this->_extendableObjects[$i]->$sName = $sValue;
                return true;
            }
        }
        return false;
    }

    function __call($sName, $aArgs = array(), &$return)
    {
        // try call itself method
        if (method_exists($this, $sName)) {
            $return = call_user_func_array(array($this, $sName), $aArgs);
            return true;
        }

        // try to call method extended from objects
        for ($i = 0; $i < count($this->_extendableObjects); $i++) {
            //if (method_callable($this->_extendableObjects[$i], $sName)) {
            if (method_exists($this->_extendableObjects[$i], $sName)) {
                $return = call_user_func_array(array(&$this->_extendableObjects[$i], $sName), $aArgs);
                return true;
            }
        }

        return false;
    }
}