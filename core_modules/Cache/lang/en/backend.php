<?php 

 /** 
 * @copyright   CONTREXX CMS - COMVATION AG 
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public 
 * @package     contrexx
 * @subpackage  coremodule_cache
 */ 
global $_ARRAYLANG; 
$_ARRAYLANG['TXT_CACHE_ERR_NOTWRITABLE'] = 'The choosen caching-directory is not writeable. Please set chmod 777 on: ';
$_ARRAYLANG['TXT_CACHE_ERR_NOTEXIST'] = 'Directory for cache system does not exist. Please check path: ';
$_ARRAYLANG['TXT_SETTINGS_MENU_CACHE'] = 'Caching';
$_ARRAYLANG['TXT_CACHE_STATS'] = 'Statistics';
$_ARRAYLANG['TXT_CACHE_CONTREXX_CACHING'] = 'Contrexx caching';
$_ARRAYLANG['TXT_CACHE_USERCACHE'] = 'Database cache engine';
$_ARRAYLANG['TXT_CACHE_OPCACHE'] = 'Program code cache engine';
$_ARRAYLANG['TXT_CACHE_PROXYCACHE'] = 'Proxy cache';
$_ARRAYLANG['TXT_CACHE_EMPTY'] = 'Empty cache';
$_ARRAYLANG['TXT_CACHE_APC'] = 'APC';
$_ARRAYLANG['TXT_CACHE_ZEND_OPCACHE'] = 'Zend OPCache';
$_ARRAYLANG['TXT_CACHE_XCACHE'] = 'xCache';
$_ARRAYLANG['TXT_CACHE_MEMCACHE'] = 'Memcache';
$_ARRAYLANG['TXT_CACHE_FILESYSTEM'] = 'FileSystem';
$_ARRAYLANG['TXT_CACHE_APC_ACTIVE_INFO'] = 'APC is active, as soon as the php directive "apc.enabled" has been set to "On".';
$_ARRAYLANG['TXT_CACHE_APC_CONFIG_INFO'] = 'If you want to use apc as a database cache engine, you have to set the php directive "apc.serializer" to "php".';
$_ARRAYLANG['TXT_CACHE_ZEND_OPCACHE_ACTIVE_INFO'] = 'Zend OPCache is active, as soon as the php directive "opcache.enable" has been set to "On".';
$_ARRAYLANG['TXT_CACHE_ZEND_OPCACHE_CONFIG_INFO'] = 'If you want to use the zend opcache as cache engine, the php directives "opcache.save_comments" and "opcache.load_comments" has to be set to "On".';
$_ARRAYLANG['TXT_CACHE_XCACHE_ACTIVE_INFO'] = 'xCache is active, as soon as the php directive "xcache.cacher" has been set to "On".';
$_ARRAYLANG['TXT_CACHE_XCACHE_CONFIG_INFO'] = 'If you want to use xCache as a database cache engine, you have to set the php directive "xcache.var_size" to a value bigger than 0. For the program code cache the php directive "xcache.size" has to be bigger than 0.';
$_ARRAYLANG['TXT_CACHE_MEMCACHE_ACTIVE_INFO'] = 'Memcache(d) is active, as soon as the Memcache(d) server is running and the configuration is correct.';
$_ARRAYLANG['TXT_CACHE_MEMCACHE_CONFIG_INFO'] = 'If you want to use Memcache(d), the configuration (IP address and port number) has to be correct.';
$_ARRAYLANG['TXT_CACHE_ENGINE'] = 'Engine';
$_ARRAYLANG['TXT_CACHE_INSTALLATION_STATE'] = 'Installed';
$_ARRAYLANG['TXT_CACHE_ACTIVE_STATE'] = 'Active';
$_ARRAYLANG['TXT_CACHE_CONFIGURATION_STATE'] = 'Configured';
$_ARRAYLANG['TXT_SAVE'] = 'Save';
$_ARRAYLANG['TXT_ACTIVATED'] = 'Activated';
$_ARRAYLANG['TXT_DEACTIVATED'] = 'Deactivated';
$_ARRAYLANG['TXT_CACHE_SETTINGS_STATUS'] = 'Cache System';
$_ARRAYLANG['TXT_CACHE_SETTINGS_STATUS_HELP'] = 'Current Status of the Caching System - Status: (on | off)';
$_ARRAYLANG['TXT_CACHE_SETTINGS_EXPIRATION'] = 'Expiration';
$_ARRAYLANG['TXT_CACHE_SETTINGS_EXPIRATION_HELP'] = 'After this period (measured in seconds) cached pages will be recreated.';
$_ARRAYLANG['TXT_CACHE_EMPTY_DESC'] = 'With a click on the button, you can remove all cached files in the caching folder. The cache will be recreated while viewing the pages.';
$_ARRAYLANG['TXT_CACHE_EMPTY_DESC_FILES_AND_ENRIES'] = 'With a click on the button, you can remove the current cache content. The cached files and entries will be recreated while viewing the pages.';
$_ARRAYLANG['TXT_CACHE_EMPTY_DESC_FILES'] = 'With a click on the button, you can remove the current cache content. The cached files will be recreated while viewing the pages.';
$_ARRAYLANG['TXT_CACHE_EMPTY_DESC_MEMCACHE'] = 'With a click on the button, you can mark the current cache content as outdated. The cached entries will be updated while viewing the pages.';
$_ARRAYLANG['TXT_CACHE_STATS_FILES'] = 'Cached Pages';
$_ARRAYLANG['TXT_CACHE_STATS_FOLDERSIZE'] = 'Folder size';
$_ARRAYLANG['TXT_STATS_CHACHE_SITE_COUNT'] = 'Cached Files';
$_ARRAYLANG['TXT_STATS_CHACHE_ENTRIES_COUNT'] = 'Cached Databaseentries';
$_ARRAYLANG['TXT_STATS_CACHE_SIZE'] = 'Ammount of stored Data';
$_ARRAYLANG['TXT_DISPLAY_CONFIGURATION'] = 'Show configuration';
$_ARRAYLANG['TXT_HIDE_CONFIGURATION'] = 'Hide configuration';
$_ARRAYLANG['TXT_CACHE_VARNISH'] = 'Varnish';
$_ARRAYLANG['TXT_CACHE_PROXY_IP'] = 'Proxy IP-Address';
$_ARRAYLANG['TXT_CACHE_PROXY_PORT'] = 'Proxy Port';
$_ARRAYLANG['TXT_SETTINGS_UPDATED'] = 'Settings have been updated.';
$_ARRAYLANG['TXT_CACHE_FOLDER_EMPTY'] = 'Cache folder has been emptied.';
$_ARRAYLANG['TXT_CACHE_EMPTY_SUCCESS'] = 'Cache has been emptied. (This does not necessarily have an effect to the statistics)';
