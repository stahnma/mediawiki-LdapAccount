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
  Usage: Edit LocalSettings.php and add the following variables
  require_once 'extensions/LdapAccount/LdapAccount.php';
  # Description: Directory Server URI with protocol (not port)
  # Default: None
  $wgDS = "ldaps://ldap.example.com";

  # Description: Directory Server Port
  # Default: 389
  $wgDSPort = 389;

  # Description: Bind Type (Anonymous or ProxyAccount)
  # Default: Anonymous
  $wgBindType = "Anonymous"; 

  # Description: Proxy Bind Account DN
  # Default: None
  $wgBindProxyDN = "uid=userid,ou=people,dc=example,dc=com";

  # Description: Proxy Bind Account Password
  # Default: None
  $wgBindProxyPW = "apassword";

  # Description: LDAP Attribute to search on 
  # Default: uid
  $wgUserAttr = "uid";

  # Description: LDAP Search Base for looking up accounts
  # Default: None
  $wgLDAPSearchBase="ou=people,dc=example,dc=com";
 **/

$wgGroupPermissions['*']['createaccount']   = false;
$wgGroupPermissions['*']['edit']            = false;

$wgExtensionCredits['other'][] = array(
        'name' => 'LdapAccount',
        'version' => '0.1',
        'author' => array('Michael Stahnke'),
        'url' => 'http://github.com/stahnma/mediawiki-LdapAccount',
        'description' => 'Restrict mediawiki to using LDAP accounts only, creates account based on
LDAP information and authenticates using LDAP.'
);



require_once("AuthPlugin.php"); 

class LdapAccount extends AuthPlugin {
 
  function setLDAPVars() {
    global $wgDSPort;
    global $wgDSPort;
    global $wgBindType;
    global $wgLDAPSearchBase;
    global $wgLDAPUserAttr;
    if (! isset($wgDSPort))
      $wgDSPort = 389;
    if (! isset($wgBindType)) 
      $wgBindType = "Anonymous";
    if (! isset($wgLDAPSearchBase))
      $wgLDAPSearchBase="ou=people,dc=example,dc=com";
    if (! isset($wgLDAPUserAttr)) 
      $wgLDAPUserAttr = "uid";
  }

  function dsConnect($bind = "yes") {
    $this->setLDAPVars();
    global $wgDS;
    global $wgDSPort;
    global $wgBindType;
    global $wgBindProxyDN;
    global $wgBindProxyPW;
    $ldap_conn = ldap_connect($wgDS, $wgDSPort);
    if (isset($ldap_conn) and $ldap_conn) {  
      ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
      ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
      if($bind == "no") 
         return $ldap_conn;
      if(isset($wgBindType) and ($wgBindType == "ProxyAccount") ) {
        if (ldap_bind($ldap_conn, $wgBindProxyDN, $wgBindProxyPW))
          return $ldap_conn;
        else 
          return false;
      }
      else {
        if (ldap_bind($ldap_conn))
          return $ldap_conn;
        else 
          return false;
      }
    }
    return false;
  }

  function userExists( $username ) {
    $this->setLDAPVars();
    global $wgLDAPSearchBase;
    global $wgLDAPUserAttr;
    $ldap_conn = $this->dsConnect();
    $results = @ldap_search( $ldap_conn, $wgLDAPSearchBase, "$wgLDAPUserAttr=$username");
    $info = @ldap_get_entries($ldap_conn,$results);
    ldap_close($ldap_conn);
    if ($info["count"] > 0)
      return true;
    else
      return false;
  }   

   function initUser( &$user ) {
    $this->setLDAPVars();
    global $wgLDAPSearchBase;
    global $wgLDAPUserAttr;
    $ldap_conn = $this->dsConnect();
    $userId = $user->getName();
    $results = ldap_search($ldap_conn, $wgLDAPSearchBase, "$wgLDAPUserAttr=$userId");
    $info = ldap_get_entries($ldap_conn,$results);
    if ($info["count"] > 0) {
      $entry = $info[0];
      @ $user->setRealName($entry["name"][0]);
      $user->setEmail($entry["mail"][0]);
    }
    ldap_close($ldap_conn);
  }

  function authenticate( $username, $password ) {
    $ldap_conn = $this->dsConnect("no");
    global $wgLDAPSearchBase;
    global $wgLDAPUserAttr;
    if (isset($ldap_conn) and ldap_bind($ldap_conn, "$wgLDAPUserAttr=$username,$wgLDAPSearchBase", $password))
      return true;
    else
      return false;
  }   

  function autoCreate() {
    return true;
  }   

  function strict() {
    return true;
  }   
  
  function allowPasswordChange() {
    return false;
  }

  function setPassword($user, $password) {
    return false;
  }
}   
?>
