<?php
/**
 * Media  Directory Form Class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/mediadir/lib/lib.class.php';

class mediaDirectoryForm extends mediaDirectoryLibrary
{
    public $intFormId;

    public $arrForms = array();

    /**
     * Constructor
     */
    function __construct($intFormId=null)
    {
        $this->intFormId = intval($intFormId);

        parent::getSettings();
        parent::getFrontendLanguages();
        $this->arrForms = self::getForms($this->intFormId);
    }

    function getForms($intFormId=null)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase, $_LANGID, $objInit;

        $arrForms = array();

        if(!empty($intFormId)) {
            $whereFormId = "form.id='".$intFormId."' AND";
        } else {
            $whereFormId = null;
        }

        $objFormsRS = $objDatabase->Execute("
            SELECT
                form.`id` AS `id`,
                form.`order` AS `order`,
                form.`picture` AS `picture`,
                form.`active` AS `active`,
                form_names.`form_name` AS `name`,
                form_names.`form_description` AS `description`
            FROM
                ".DBPREFIX."module_".$this->moduleTablePrefix."_forms AS form,
                ".DBPREFIX."module_".$this->moduleTablePrefix."_form_names AS form_names
            WHERE
                ($whereFormId form_names.form_id=form.id)
            AND
                (form_names.lang_id='".$_LANGID."')
            ORDER BY
                `order` ASC
            ");

        if ($objFormsRS !== false) {
			while (!$objFormsRS->EOF) {

			    $arrForm = array();
                $arrFormName = array();
                $arrFormDesc = array();

                //get lang attributes
                $arrFormName[0] = $objFormsRS->fields['name'];
                $arrFormDesc[0] = $objFormsRS->fields['description'];

                $objFormAttributesRS = $objDatabase->Execute("
                    SELECT
                        `lang_id` AS `lang_id`,
                        `form_name` AS `name`,
                        `form_description` AS `description`
                    FROM
                        ".DBPREFIX."module_".$this->moduleTablePrefix."_form_names
                    WHERE
                        form_id=".$objFormsRS->fields['id']."
                ");

                if ($objFormAttributesRS !== false) {
                    while (!$objFormAttributesRS->EOF) {
                        $arrFormName[$objFormAttributesRS->fields['lang_id']] = htmlspecialchars($objFormAttributesRS->fields['name'], ENT_QUOTES, CONTREXX_CHARSET);
                        $arrFormDesc[$objFormAttributesRS->fields['lang_id']] = htmlspecialchars($objFormAttributesRS->fields['description'], ENT_QUOTES, CONTREXX_CHARSET);

                        $objFormAttributesRS->MoveNext();
                    }
                }

                $arrForm['formId'] = intval($objFormsRS->fields['id']);
                $arrForm['formOrder'] = intval($objFormsRS->fields['order']);
                $arrForm['formPicture'] = htmlspecialchars($objFormsRS->fields['picture'], ENT_QUOTES, CONTREXX_CHARSET);
                $arrForm['formName'] = $arrFormName;
                $arrForm['formDescription'] = $arrFormDesc;
                $arrForm['formActive'] = intval($objFormsRS->fields['active']);

                $arrForms[$objFormsRS->fields['id']] = $arrForm;
                $objFormsRS->MoveNext();
			}
        }

        return $arrForms;
    }



    function listForms($objTpl, $intView, $intFormId=null)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase, $_LANGID;

        $i = 0;

        switch ($intView) {
            case 1:
                //settings overview
                if(!empty($this->arrForms)){
                    foreach ($this->arrForms as $key => $arrForm) {
                		//get status
                		if($arrForm['formActive'] == 1) {
                		    $strStatus = 'images/icons/status_green.gif';
                		    $intStatus = 0;
                		} else {
                		    $strStatus = 'images/icons/status_red.gif';
                		    $intStatus = 1;
                		}

        			    //parse data variables
                        $objTpl->setVariable(array(
                            $this->moduleLangVar.'_FORM_ROW_CLASS' => $i%2==0 ? 'row1' : 'row2',
                            $this->moduleLangVar.'_FORM_ID' => $arrForm['formId'],
                            $this->moduleLangVar.'_FORM_TITLE' => $arrForm['formName'][0],
                            $this->moduleLangVar.'_FORM_DESCRIPTION' => $arrForm['formDescription'][0],
                            $this->moduleLangVar.'_FORM_ORDER' => $arrForm['formOrder'],
                            $this->moduleLangVar.'_FORM_STATUS' => $strStatus,
                            $this->moduleLangVar.'_FORM_SWITCH_STATUS' => $intStatus,
                        ));

                        $i++;
                        $objTpl->parse($this->moduleName.'FormTemplateList');
                        $objTpl->hideBlock($this->moduleName.'FormTemplateNoEntries');
                        $objTpl->clearVariables();
                    }
                } else {
                    $objTpl->setGlobalVariable(array(
                        'TXT_'.$this->moduleLangVar.'_NO_ENTRIES_FOUND' => $_ARRAYLANG['TXT_MEDIADIR_NO_ENTRIES_FOUND']
                    ));

                    $objTpl->touchBlock($this->moduleName.'FormTemplateNoEntries');
                    $objTpl->clearVariables();
                }
                break;
            case 2:
                //form selector backend (add entry view)
                $arrDropdownOptions[0] = $_ARRAYLANG['TXT_MEDIADIR_CHOOSE'];

                foreach ($this->arrForms as $key => $arrForm) {
                    if($arrForm['formActive'] == 1) {
                        $arrDropdownOptions[$arrForm['formId']] = $arrForm['formName'][0];
                    }
                }

                $strDropdown = $this->buildDropdownmenu($arrDropdownOptions, null);

                //parse data variables
                $objTpl->setVariable(array(
                    'TXT_'.$this->moduleLangVar.'_CHOOSE_FORM' => $_ARRAYLANG['TXT_MEDIADIR_CHOOSE_FORM'],
                    $this->moduleLangVar.'_FORM_LIST' => '<select onchange="document.entryModfyForm.submit();" name="selectedFormId" style="width: 302px">'.$strDropdown."</select>",
                ));

                $objTpl->parse($this->moduleName.'FormList');
                $objTpl->clearVariables();

                break;
            case 3:
                //form selector frontend (add entry view)

                foreach ($this->arrForms as $key => $arrForm) {
                    if($arrForm['formActive'] == 1) {
                        //parse data variables
                        $objTpl->setVariable(array(
                            $this->moduleLangVar.'_FORM_ROW_CLASS' => $i%2==0 ? 'row1' : 'row2',
                            'TXT_'.$this->moduleLangVar.'_FORM_ID' => $arrForm['formId'],
                            'TXT_'.$this->moduleLangVar.'_FORM_TITLE' => $arrForm['formName'][0],
                            'TXT_'.$this->moduleLangVar.'_FORM_DESCRIPTION' => $arrForm['formDescription'][0],
                            'TXT_'.$this->moduleLangVar.'_FORM_IMAGE' => '<img src="'.$arrForm['formPicture'].'" alt="'.$arrForm['formName'][0].'" />',
                            'TXT_'.$this->moduleLangVar.'_FORM_IMAGE_SRC' => $arrForm['formPicture'],
                            'TXT_'.$this->moduleLangVar.'_FORM_IMAGE_SRC_THUMB' => $arrForm['formPicture']."thumb",
                            'TXT_'.$this->moduleLangVar.'_FORM_IMAGE_THUMB' => '<img src="'.$arrForm['formPicture'].'.thumb" alt="'.$arrForm['formName'][0].'" />',
                        ));

                        $i++;
                        $objTpl->parse($this->moduleName.'FormList');
                        $objTpl->clearVariables();
                    }
                }


                $objTpl->parse($this->moduleName.'Forms');
                break;
        }
    }



    function saveForm($arrData, $intFormId=null)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase, $_LANGID;

        $intId = intval($intFormId);
        $strPicture = contrexx_addslashes(contrexx_strip_tags($arrData['formImage']));
        $intActive = intval($arrData['categoryActive']);

        $arrName = $arrData['formName'];
        $arrDescription = $arrData['formDescription'];

        if(empty($intId)) {
            //insert new form
            $objInsertAttributes = $objDatabase->Execute("
                INSERT INTO
                    ".DBPREFIX."module_".$this->moduleTablePrefix."_forms
                SET
                    `order`='99',
                    `picture`='".$strPicture."',
                    `active`='0'
            ");

            if($objInsertAttributes !== false) {
                $intId = $objDatabase->Insert_ID();

                foreach ($this->arrFrontendLanguages as $key => $arrLang) {
                    if(empty($arrName[0])) $arrName[0] = "";

                    $strName = $arrName[$arrLang['id']];
                    $strDescription = $arrDescription[$arrLang['id']];

                    if(empty($strName)) $strName = contrexx_addslashes(contrexx_strip_tags($arrName[0]));
                    if(empty($strDescription)) $strDescription = contrexx_addslashes(contrexx_strip_tags($arrDescription[0]));

                    $objInsertNames = $objDatabase->Execute("
                        INSERT INTO
                            ".DBPREFIX."module_".$this->moduleTablePrefix."_form_names
                        SET
                            `lang_id`='".intval($arrLang['id'])."',
                            `form_id`='".intval($intId)."',
                            `form_name`='".contrexx_addslashes(contrexx_strip_tags($strName))."',
                            `form_description`='".contrexx_addslashes(contrexx_strip_tags($strDescription))."'
                    ");
                }



                $objCreateCatSelectors = $objDatabase->Execute("
                    INSERT INTO
                        ".DBPREFIX."module_".$this->moduleTablePrefix."_order_rel_forms_selectors
                    SET
                        `selector_id`='9',
                        `form_id`='".intval($intId)."',
                        `selector_order`='0',
                        `exp_search`='1'
                ");

                $objCreateLevelSelectors = $objDatabase->Execute("
                    INSERT INTO
                        ".DBPREFIX."module_".$this->moduleTablePrefix."_order_rel_forms_selectors
                    SET
                        `selector_id`='10',
                        `form_id`='".intval($intId)."',
                        `selector_order`='1',
                        `exp_search`='1'
                ");

                //permissions
                parent::getCommunityGroups();
                foreach ($this->arrCommunityGroups as $intGroupId => $arrGroup) {
                    $objInsertPerm = $objDatabase->Execute("
                        INSERT INTO
                            ".DBPREFIX."module_".$this->moduleTablePrefix."_settings_perm_group_forms
                        SET
                            `group_id`='".intval($intGroupId)."',
                            `form_id`='".intval($intId)."',
                            `status_group`='1'
                    ");
                }

                if($objInsertNames !== false && $objCreateCatSelectors !== false && $objCreateLevelSelectors !== false) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }

        } else {
            //update form
            $objUpdateAttributes = $objDatabase->Execute("
                UPDATE
                    ".DBPREFIX."module_".$this->moduleTablePrefix."_forms
                SET
                    `picture`='".$strPicture."'
                WHERE
                    `id`='".$intId."'
            ");

            $objDefaultLang = $objDatabase->Execute("
                SELECT
                    `form_name` AS `name`,
                    `form_description` AS `description`
                FROM
                    ".DBPREFIX."module_".$this->moduleTablePrefix."_form_names
                WHERE
                    lang_id=".$_LANGID."
                    AND `form_id` = '".$intId."'
                LIMIT
                    1
            ");

            if ($objDefaultLang !== false) {
                $strOldDefaultName = $objDefaultLang->fields['name'];
                $strOldDefaultDescription = $objDefaultLang->fields['description'];
            }

            //permissions
            $objDeletePerm = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_settings_perm_group_forms WHERE form_id='".$intId."'");

            foreach ($arrData['settingsPermGroupForm'][$intId] as $intGroupId => $intGroupStatus) {
                $objInsertPerm = $objDatabase->Execute("
                    INSERT INTO
                        ".DBPREFIX."module_".$this->moduleTablePrefix."_settings_perm_group_forms
                    SET
                        `group_id`='".intval($intGroupId)."',
                        `form_id`='".intval($intId)."',
                        `status_group`='".intval($intGroupStatus)."'
                ");
            }

            $objDeleteNames = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_form_names WHERE form_id='".$intId."'");

            if($objInsertNames !== false) {
                foreach ($this->arrFrontendLanguages as $key => $arrLang) {
                    $strName = $arrName[$arrLang['id']];
                    $strDescription = $arrDescription[$arrLang['id']];

                    if($arrLang['id'] == $_LANGID) {
                        if($arrName[0] != $strOldDefaultName) $strName = $arrName[0];
                        if($arrName[$arrLang['id']] != $strOldDefaultName) $strName = $arrName[$arrLang['id']];

                        if($arrDescription[0] != $strOldDefaultDescription) $strDescription = $arrDescription[0];
                        if($arrDescription[$arrLang['id']] != $strOldDefaultDescription) $strDescription = $arrDescription[$arrLang['id']];
                    }

                    if(empty($strName)) $strName = $arrName[0];
                    if(empty($strDescription)) $strDescription = $arrDescription[0];

                    $objInsertNames = $objDatabase->Execute("
                        INSERT INTO
                            ".DBPREFIX."module_".$this->moduleTablePrefix."_form_names
                        SET
                            `lang_id`='".intval($arrLang['id'])."',
                            `form_id`='".intval($intId)."',
                            `form_name`='".contrexx_addslashes(contrexx_strip_tags($strName))."',
                            `form_description`='".contrexx_addslashes(contrexx_strip_tags($strDescription))."'
                    ");
                }

                if($objInsertNames !== false) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

    }



    function deleteForm($intFormId)
    {
        global $objDatabase;
        $arrEntryIds = array();

        //delete entries
        $objRSEntriesDelete = $objDatabase->Execute("SELECT
                                                        id
                                                     FROM
                                                        ".DBPREFIX."module_".$this->moduleTablePrefix."_entries
                                                     WHERE
                                                        `form_id`='".intval($intFormId)."'
                                                    ");
        if ($objRSEntriesDelete !== false) {
			while (!$objRSEntriesDelete->EOF) {
			    $arrEntryIds[] = $objRSEntriesDelete->fields['id'];
                $objRSEntriesDelete->MoveNext();
			}

			foreach ($arrEntryIds as $key => $intEntryId) {
			    //delete rel levels
			    $objRSEntryDeleteRelLevels = $objDatabase->Execute("DELETE FROM
			                                                             ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_levels
			                                                        WHERE
			                                                             `entry_id`='".intval($intEntryId)."'
			                                                        ");

			    //delete rel categories
			    $objRSEntryDeleteRelCategories = $objDatabase->Execute("DELETE FROM
			                                                             ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_categories
			                                                        WHERE
			                                                             `entry_id`='".intval($intEntryId)."'
			                                                        ");

			    //delete rel inputfields
			    $objRSEntryDeleteRelInputfields = $objDatabase->Execute("DELETE FROM
			                                                             ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields
			                                                        WHERE
			                                                             `entry_id`='".intval($intEntryId)."'
			                                                        ");

			    if ($objRSEntryDeleteRelLevels !== false && $objRSEntryDeleteRelCategories !== false && $objRSEntryDeleteRelInputfields !== false) {
			        //delete entries
			        $objRSEntryDeleteRelInputfields = $objDatabase->Execute("DELETE FROM
			                                                                     ".DBPREFIX."module_".$this->moduleTablePrefix."_entries
        			                                                         WHERE
        			                                                             `form_id`='".intval($intFormId)."'
        			                                                         ");
			        if ($objRSEntryDeleteRelInputfields === false) {
			            return false;
			        }
			    } else {
			        return false;
			    }
			}
        } else {
            return false;
        }

        //delete selector order
        $objRSEntriesDelete = $objDatabase->Execute("DELETE FROM
                                                        ".DBPREFIX."module_".$this->moduleTablePrefix."_order_rel_forms_selectors
                                                     WHERE
                                                        `form_id`='".intval($intFormId)."'
                                                    ");

        //delete inputfields
        $objRSEntriesDelete = $objDatabase->Execute("DELETE FROM
                                                        ".DBPREFIX."module_".$this->moduleTablePrefix."_inputfields
                                                     WHERE
                                                        `form`='".intval($intFormId)."'
                                                    ");

        //delete inputfields names
        $objRSEntriesDelete = $objDatabase->Execute("DELETE FROM
                                                        ".DBPREFIX."module_".$this->moduleTablePrefix."_inputfield_names
                                                     WHERE
                                                        `form_id`='".intval($intFormId)."'
                                                    ");

        //delete permissions
        $objRSEntriesDelete = $objDatabase->Execute("DELETE FROM
                                                        ".DBPREFIX."module_".$this->moduleTablePrefix."_settings_perm_group_forms
                                                     WHERE
                                                        `form_id`='".intval($intFormId)."'
                                                    ");

        //delete forms
        $objRSEntriesDelete = $objDatabase->Execute("DELETE FROM
                                                        ".DBPREFIX."module_".$this->moduleTablePrefix."_forms
                                                     WHERE
                                                        `id`='".intval($intFormId)."'
                                                    ");

        return true;
    }



    function saveOrder($arrData) {
        global $objDatabase;

        foreach($arrData['formsOrder'] as $intFormId => $intFormOrder) {
            $objRSFormOrder = $objDatabase->Execute("UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_forms SET `order`='".intval($intFormOrder)."' WHERE `id`='".intval($intFormId)."'");

            if ($objRSFormOrder === false) {
                return false;
            }
        }

        return true;
    }
}
?>