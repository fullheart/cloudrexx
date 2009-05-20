<?php
/**
 * Digital Asset Management
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_downloads
 * @version     1.0.0
 */

/**
 * @ignore
 */
require_once dirname(__FILE__).'/Category.class.php';
/**
 * @ignore
 */
require_once dirname(__FILE__).'/Download.class.php';
/**
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/System.class.php';

/**
 * Digital Asset Management Library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_downloads
 * @version     1.0.0
 */
class DownloadsLibrary
{

    /**
     * File extensions that are allowed to upload
     *
     * This array contains all file extensions that are allowed
     * to be uploaded. If a file's file extensions is not listed
     * in this array then the contact request will be blocked and
     * a error message will be return instead.
     */
    var $enabledUploadFileExtensions = array(
        "txt","doc","xls","pdf","ppt","gif","jpg","png","xml",
        "odt","ott","sxw","stw","dot","rtf","sdw","wpd","jtd",
        "jtt","hwp","wps","ods","ots","sxc","stc","dif","dbf",
        "xlw","xlt","sdc","vor","sdc","cvs","slk","wk1","wks",
        "123","odp","otp","sxi","sti","pps","pot","sxd","sda",
        "sdd","sdp","cgm","odg","otg","sxd","std","dxf","emf",
        "eps","met","pct","sgf","sgv","svm","wmf","bmp","jpeg",
        "jfif","jif","jpe","pbm","pcx","pgm","ppm","psd","ras",
        "tga","tif","tiff","xbm","xpm","pcd","oth","odm","sxg",
        "sgl","odb","odf","sxm","smf","mml","zip","rar"
    );
    protected $defaultCategoryImage = array();
    protected $defaultDownloadImage = array();
    protected $arrPermissionTypes = array(
        'getReadAccessId'                   => 'read',
        'getAddSubcategoriesAccessId'       => 'add_subcategories',
        'getManageSubcategoriesAccessId'    => 'manage_subcategories',
        'getAddFilesAccessId'               => 'add_files',
        'getManageFilesAccessId'            => 'manage_files'
    );

    protected $searchKeyword;
    protected $arrConfig = array(
        'overview_cols_count'           => 2,
        'overview_max_subcats'          => 5,
        'use_attr_size'                 => 1,
        'use_attr_license'              => 1,
        'use_attr_version'              => 1,
        'use_attr_author'               => 1,
        'use_attr_website'              => 1,
        'most_viewed_file_count'        => 5,
        'most_downloaded_file_count'    => 5,
        'most_popular_file_count'       => 5,
        'newest_file_count'             => 5,
        'updated_file_count'            => 5,
        'new_file_time_limit'           => 604800,
        'updated_file_time_limit'       => 604800
    );

    public function __construct()
    {
        $this->initSettings();
        $this->initSearch();
        $this->initDefaultCategoryImage();
        $this->initDefaultDownloadImage();
    }

    private function initSettings()
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute('SELECT `name`, `value` FROM `'.DBPREFIX.'module_downloads_settings`');
        if ($objResult) {
            while (!$objResult->EOF) {
                $this->arrConfig[$objResult->fields['name']] = $objResult->fields['value'];
                $objResult->MoveNext();
            }
        }
    }

    protected function initSearch()
    {
        $this->searchKeyword = empty($_REQUEST['downloads_search_keyword']) ? '' : $_REQUEST['downloads_search_keyword'];
    }

    protected function initDefaultCategoryImage()
    {
        $this->defaultCategoryImage['src'] = ASCMS_DOWNLOADS_IMAGES_WEB_PATH.'/no_picture.gif';

        $imageSize = getimagesize(ASCMS_PATH.$this->defaultCategoryImage['src']);

        $this->defaultCategoryImage['width'] = $imageSize[0];
        $this->defaultCategoryImage['height'] = $imageSize[1];
    }

    protected function initDefaultDownloadImage()
    {
        $this->defaultDownloadImage = $this->defaultCategoryImage;
    }

    protected function updateSettings()
    {
        global $objDatabase;

        foreach ($this->arrConfig as $key => $value) {
            $objDatabase->Execute("UPDATE `".DBPREFIX."module_downloads_settings` SET `value` = '".addslashes($value)."' WHERE `name` = '".$key."'");
        }
    }

    protected function getCategoryMenu($accessType, $selectedCategory, $selectionText, $attrs = null, $categoryId = null)
    {
        global $_LANGID, $_ARRAYLANG;

        $objCategory = Category::getCategories(null, null, array('order' => 'ASC', 'name' => 'ASC', 'id' => 'ASC'));
        $arrCategories = array();

        switch ($accessType) {
            case 'read':
                $accessCheckFunction = 'getReadAccessId';
                break;

            case 'add_subcategory':
                $accessCheckFunction = 'getAddSubcategoriesAccessId';
                break;
        }

        while (!$objCategory->EOF) {
            // TODO: getVisibility() < should only be checked if the user isn't an admin or so
            if ($objCategory->getVisibility() || Permission::checkAccess($objCategory->getReadAccessId(), 'dynamic', true)) {
                $arrCategories[$objCategory->getParentId()][] = array(
                    'id'        => $objCategory->getId(),
                    'name'      => $objCategory->getName($_LANGID),
                    'owner_id'  => $objCategory->getOwnerId(),
                    'access_id' => $objCategory->{$accessCheckFunction}(),
                    'is_child'  => $objCategory->check4Subcategory($categoryId)
                );
            }

            $objCategory->next();
        }

        $menu = '<select name="downloads_category_parent_id"'.(!empty($attrs) ? ' '.$attrs : '').'>';
        $menu .= '<option value="0"'.(!$selectedCategory ? ' selected="selected"' : '').($accessType != 'read' && !Permission::checkAccess(142, 'static', true) ? ' disabled="disabled"' : '').' style="border-bottom:1px solid #000;">'.$selectionText.'</option>';

        $menu .= $this->parseCategoryTreeForMenu($arrCategories, $selectedCategory, $categoryId);

        while (count($arrCategories)) {
            reset($arrCategories);
            $menu .= $this->parseCategoryTreeForMenu($arrCategories, $selectedCategory, $categoryId, key($arrCategories));
        }
        $menu .= '</select>';

        return $menu;
    }

    private function parseCategoryTreeForMenu(&$arrCategories, $selectedCategory, $categoryId = null, $parentId = 0, $level = 0)
    {
        $options = '';

        if (!isset($arrCategories[$parentId])) {
            return $options;
        }

        $length = count($arrCategories[$parentId]);
        for ($i = 0; $i < $length; $i++) {
            $options .= '<option value="'.$arrCategories[$parentId][$i]['id'].'"'
                    .($arrCategories[$parentId][$i]['id'] == $selectedCategory ? ' selected="selected"' : '')
                    .($arrCategories[$parentId][$i]['id'] == $categoryId || $arrCategories[$parentId][$i]['is_child'] ? ' disabled="disabled"' : (
                        // managers are allowed to see the content of every category
                        Permission::checkAccess(142, 'static', true)
                        // the category isn't protected => everyone is allowed to the it's content
                        || !$arrCategories[$parentId][$i]['access_id']
                        // the category is protected => only those who have the sufficent permissions are allowed to see it's content
                        || Permission::checkAccess($arrCategories[$parentId][$i]['access_id'], 'dynamic', true)
                        // the owner is allowed to see the content of the category
                        || ($objFWUser = FWUser::getFWUserObject()) && $objFWUser->objUser->login() && $arrCategories[$parentId][$i]['owner_id'] == $objFWUser->objUser->getId() ? '' : ' disabled="disabled"')
                    )
                .'>'
                    .str_repeat('&nbsp;', $level * 4).htmlentities($arrCategories[$parentId][$i]['name'], ENT_QUOTES, CONTREXX_CHARSET)
                .'</option>';
            if (isset($arrCategories[$arrCategories[$parentId][$i]['id']])) {
                $options .= $this->parseCategoryTreeForMenu($arrCategories, $selectedCategory, $categoryId, $arrCategories[$parentId][$i]['id'], $level + 1);
            }
        }

        unset($arrCategories[$parentId]);

        return $options;
    }

    protected function getParsedCategoryListForDownloadAssociation( )
    {
        global $_LANGID, $_ARRAYLANG;

        $objCategory = Category::getCategories(null, null, array('order' => 'ASC', 'name' => 'ASC', 'id' => 'ASC'));
        $arrCategories = array();

        while (!$objCategory->EOF) {
                $arrCategories[$objCategory->getParentId()][] = array(
                    'id'                    => $objCategory->getId(),
                    'name'                  => $objCategory->getName($_LANGID),
                    'owner_id'              => $objCategory->getOwnerId(),
                    'add_files_access_id'     => $objCategory->getAddFilesAccessId(),
                    'manage_files_access_id'  => $objCategory->getManageFilesAccessId()
                );

            $objCategory->next();
        }

       $arrParsedCategories = $this->parseCategoryTreeForDownloadAssociation($arrCategories);

        while (count($arrCategories)) {
            reset($arrCategories);
            $arrParsedCategories = array_merge($arrParsedCategories, $this->parseCategoryTreeForDownloadAssociation($arrCategories, key($arrCategories)));
        }

        return $arrParsedCategories;
    }

    private function parseCategoryTreeForDownloadAssociation(&$arrCategories, $parentId = 0, $level = 0)
    {
        $arrParsedCategories = array();

        if (!isset($arrCategories[$parentId])) {
            return $arrParsedCategories;
        }

        $length = count($arrCategories[$parentId]);
        for ($i = 0; $i < $length; $i++) {
            $arrParsedCategories[] = array_merge($arrCategories[$parentId][$i], array('level' => $level));
            if (isset($arrCategories[$arrCategories[$parentId][$i]['id']])) {
                $arrParsedCategories = array_merge($arrParsedCategories, $this->parseCategoryTreeForDownloadAssociation($arrCategories, $arrCategories[$parentId][$i]['id'], $level + 1));
            }
        }

        unset($arrCategories[$parentId]);

        return $arrParsedCategories;
    }


    protected function getParsedUsername($userId)
    {
        global $_ARRAYLANG;

        $objFWUser = FWUser::getFWUserObject();
        if ($objUser = $objFWUser->objUser->getUser($userId)) {
            if ($objUser->getProfileAttribute('firstname') || $objUser->getProfileAttribute('lastname')) {
                $author = $objUser->getProfileAttribute('firstname').' '.$objUser->getProfileAttribute('lastname').' ('.$objUser->getUsername().')';
            } else {
                $author = $objUser->getUsername();
            }
            $author = htmlentities($author, ENT_QUOTES, CONTREXX_CHARSET);
        } else {
            $author = $_ARRAYLANG['TXT_DOWNLOADS_UNKNOWN'];
        }

        return $author;
    }

    protected function getUserDropDownMenu($selectedUserId, $userId)
    {
        $menu = '<select name="downloads_category_owner_id" onchange="document.getElementById(\'downloads_category_owner_config\').style.display = this.value == '.$userId.' ? \'none\' : \'\'" style="width:300px;">';
        $objFWUser = FWUser::getFWUserObject();
        $objUser = $objFWUser->objUser->getUsers();
        while (!$objUser->EOF) {
            $menu .= '<option value="'.$objUser->getId().'"'.($objUser->getId() == $selectedUserId ? ' selected="selected"' : '').'>'.$this->getParsedUsername($objUser->getId()).'</option>';
            $objUser->next();
        }
        $menu .= '</select>';

        return $menu;
    }

    protected function getDownloadMimeTypeMenu($selectedType)
    {
        global $_ARRAYLANG;

        $menu = '<select name="downloads_download_mime_type" id="downloads_download_mime_type" style="width:300px;">';
        $arrMimeTypes = Download::$arrMimeTypes;
        foreach ($arrMimeTypes as $type => $arrMimeType) {
            $menu .= '<option value="'.$type.'"'.($type == $selectedType ? ' selected="selected"' : '').'>'.$_ARRAYLANG[$arrMimeType['description']].'</option>';
        }

        return $menu;
    }
}
?>
