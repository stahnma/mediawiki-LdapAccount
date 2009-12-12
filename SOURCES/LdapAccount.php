<?php 

/**
 *  DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
 *                  Version 2, December 2004
 *
 * Copyright (C) 2004 Sam Hocevar
 * 14 rue de Plaisance, 75014 Paris, France
 * Everyone is permitted to copy and distribute verbatim or modified
 * copies of this license document, and changing it is allowed as long
 * as the name is changed.
 * 
 *         DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
 *  TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION
 * 
 * 0. You just DO WHAT THE FUCK YOU WANT TO.
**/


/** 
 * Usage: Edit LocalSettings.php and add the following variables
 * require_once 'extensions/LdapAccountPlugin.php';
 * $wgDS = Directory Server with protocol (e.g. ldaps://ldap.example.com)
 * $wgBindType = (Anonymous or ProxyAccount)
 * $wgBindProxyDN = "uid=userid,ou=people,dc=example,dc=com"
 * $wgBindProxyPW = "apassword"
 * $wgDSPort = 389 or 636 (defaults to 389)
 * $wgBaseDN = "dc=example,dc=com"
 * 
 * 
 * 
 **/


require_once("AuthPlugin.php"); 

class LdapAccountPlugin extends AuthPlugin
{
  function userExists( $username )
  {
    $userFoundInLdap = false;
    $ds = 'ldaps://odin.websages.com';
    $ldap_conn = ldap_connect($ds, 636);
    if (isset($ldap_conn) and $ldap_conn)
    {
      ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
      ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
      ldap_bind($ldap_conn, 'cn=LDAP Anonymous,ou=Special,dc=websages,dc=com', '8de47d5aa7d61e92c577d8156b966583f6d7d75d714a3b99fca4fb2f8bfe97c6');
      $results = ldap_search(
          $ldap_conn,
          "OU=people,dc=websages,dc=com",
          "uid=$username");
      $info = ldap_get_entries($ldap_conn,$results);
      if ($info["count"] > 0)
      {
        $userFoundInLdap = true;
      }
      ldap_close($ldap_conn);
    }
    return $userFoundInLdap;
  }   

  function authenticate( $username, $password )
  {
    /* need ldap authentication here */ 
    
    if (isset($ldap_conn) and ldap_bind($ldap_conn, "uid=$username,ou=People,dc=websages,dc=com", $password))
    {
      return true;
    }
    else
    {
      return false;
    }
  }   

  function autoCreate()
  {
    return true;
  }   

  function strict()
  {
    return false;
  }   

  function initUser( &$user )
  {
    $ds = 'ldaps://odin.websages.com';
    $ldap_conn = ldap_connect($ds, 636);
    if (isset($ldap_conn) and $ldap_conn)
    {
      ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
      ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
      ldap_bind($ldap_conn,
                'cn=LDAP Anonymous,ou=Special,dc=websages,dc=com',
                '8de47d5aa7d61e92c577d8156b966583f6d7d75d714a3b99fca4fb2f8bfe97c6');
      $userId = $user->getName();
      $results = ldap_search($ldap_conn, "OU=people,dc=websages,dc=com", "uid=$userId");
      $info = ldap_get_entries($ldap_conn,$results);
      if ($info["count"] > 0)
      {
        $entry = $info[0];
        $user->setRealName($entry["name"][0]);
        $user->setEmail($entry["mail"][0]);
      }
      ldap_close($ldap_conn);
    }
  }
}   

?>
