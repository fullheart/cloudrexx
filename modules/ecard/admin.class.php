<?php
	class ecard {
	    var $_objTpl;
	    var $_pageTitle;
	    var $strErrMessage = '';
	    var $strOkMessage = '';
	
	    /**
	    * PHP5 constructor
	    *
	    * @global object $objTemplate
	    * @global array $_ARRAYLANG
	    */
	    function __construct() {
	        global $_ARRAYLANG, $_CORELANG, $objTemplate;
	
	        $this->_objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/ecard/template');
	        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
	                
	        $objTemplate->setVariable(
	        	"CONTENT_NAVIGATION", '<a href="index.php?cmd=ecard">' . $_ARRAYLANG['TXT_MOTIVE_SELECTION'] . '</a><a href="index.php?cmd=ecard&amp;act=settings">' . $_ARRAYLANG['TXT_SETTINGS'] . '</a>'
	        );
	    }
	
	    
	    /**
	    * Set the backend page
	    *
	    * @access public
	    * @global object $objTemplate
	    * @global array $_ARRAYLANG
	    */
	    function getPage() {
	        global $objTemplate;
	        
	        $_GET['act'] = (isset($_GET['act'])) ? $_GET['act'] : '';
	
	        switch ($_GET['act']) {
	            case 'settings':
	            	$this->settings();
	            	break;
	            default:
	                $this->setMotives();
	        }
	
	        $objTemplate->setVariable(array(
	            'CONTENT_OK_MESSAGE'        => $this->strOkMessage,
	            'CONTENT_STATUS_MESSAGE'	=> $this->strErrMessage,
	            'ADMIN_CONTENT'             => $this->_objTpl->get(),
	            'CONTENT_TITLE'             => $this->_pageTitle,
	        ));
			
	        return $this->_objTpl->get();
	    }
	    
	    function setMotives() {
			global $objTemplate, $objDatabase, $_ARRAYLANG;
			$this->_objTpl->loadTemplateFile('module_ecard_overview.html',true,true);
			$this->_pageTitle = $_ARRAYLANG['TXT_MOTIVE_SELECTION'];
			
			
			/************************/
			/* Initialize variables */
			/************************/
			$_POST['saveMotives'] = isset($_POST['saveMotives']) ? $_POST['saveMotives'] : '';
				
			
			/*******************/
			/* Update progress */
			/*******************/
			if ($_POST['saveMotives']) {
				$i = 1;
				$iArray = 0;
				$motiveInputArray = $_POST['motiveInputArray'];
				
				while ($i <= 9) {
					$query = "	UPDATE
									".DBPREFIX."module_ecard_settings
								SET
									setting_value = '"  . $motiveInputArray[$iArray] . "'
								WHERE
									setting_name = 'motive_".$i."';";	
					
					$objResult = $objDatabase->Execute($query);	
	
					
					/************************************************/
					/* Create optimized picture for e-card dispatch */
					/************************************************/
					$motiveInputArray[$iArray] = str_replace(ASCMS_PATH_OFFSET, "", $motiveInputArray[$iArray]);
					if ($motiveInputArray[$iArray] != '' && (file_exists('..' . $motiveInputArray[$iArray]))) {
						$this->resizeMotive(2, $motiveInputArray[$iArray], ASCMS_ECARD_OPTIMIZED_PATH);		
					}			
					$i++;
					$iArray++;
				}
				$this->_objTpl->setVariable(array(
					'CONTENT_OK_MESSAGE'	=> $this->strOkMessage = $_ARRAYLANG['TXT_DATA_SAVED']
				));
			}		
			
			
			/********************/
			/* Display progress */
			/********************/
			$query = "	SELECT *
						FROM
							".DBPREFIX."module_ecard_settings
						WHERE
							setting_name
						LIKE
							'motive_%'
						ORDER BY
							setting_name ASC";
			
			$objResult = $objDatabase->Execute($query);
			
			$i = 1;
			
					
			/*********************/
			/* Create thumbnails */
			/*********************/
			while (!$objResult->EOF) {
				$motivePath = $objResult->fields['setting_value']; // returns this => /x/y/zz.jpg
				$motivePath = str_replace(ASCMS_PATH_OFFSET, "", $motivePath);
				$motiveFilename = str_replace('/', '', strrchr($motivePath, "/")); //returns this => xxx.jpg
				$thumbnail = ASCMS_ECARD_THUMBNAIL_WEB_PATH . $motiveFilename;
				
				if ($motivePath != "") {
					if (file_exists(".." . $motivePath)) {
						$this->resizeMotive(1, $motivePath, ASCMS_ECARD_THUMBNAIL_PATH);
						$motive = '<img style="border:1px solid #0A50A1;" src="' . $thumbnail . '" alt="' . $objResult->fields['setting_value'] . '" />';
					}	
				} else {
					$thumbnail = ASCMS_ECARD_THUMBNAIL_WEB_PATH . "no_picture.gif";
					$motive = '<img style="border:1px solid #0A50A1;" src="' . $thumbnail . '" alt="' . $thumbnail . '" />';
				} 
					
				
				/*******************************/
				/* Initialize DATA placeholder */
				/*******************************/		
				$this->_objTpl->setVariable(array(
					'MOTIVE_PATH'	=> $objResult->fields['setting_value'],
					'MOTIVE_ID'     => $objResult->fields['id'],
					'MOTIVE'	    => $motive
				));
				
				$this->_objTpl->parse('motiveBlock');		
				$objResult->MoveNext();
			}
					
			
			/*******************************/
			/* Initialize TEXT placeholder */
			/*******************************/
			$this->_objTpl->setVariable(array(
				'TXT_SAVE'   		=> $_ARRAYLANG['TXT_SAVE'],
				'TXT_DELETE_MOTIVE' => $_ARRAYLANG['TXT_DELETE_MOTIVE'],
				'TXT_PICTURE'   	=> $_ARRAYLANG['TXT_PICTURE'],
				'TXT_PATH'   		=> $_ARRAYLANG['TXT_PATH'],
				'TXT_BROWSE'   		=> $_ARRAYLANG['TXT_BROWSE'],
				'TXT_CHOOSE'   		=> $_ARRAYLANG['TXT_CHOOSE'],
				'TXT_DELETE'   		=> $_ARRAYLANG['TXT_DELETE']
	
			));
		}
		
		function settings() {
			global $objTemplate, $objDatabase, $_ARRAYLANG;
			$this->_objTpl->loadTemplateFile('module_ecard_settings.html',true,true);
			$this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
			
			/************************/
			/* Initialize variables */
			/************************/
			$_POST['saveSettings'] = isset($_POST['saveSettings']) ? $_POST['saveSettings'] : '';
			$_POST['deleteEcards'] = isset($_POST['deleteEcards']) ? $_POST['deleteEcards'] : '';
			
			$maxHeight = 0;
			$maxWidth = 0;
			$maxWidthThumb = 0;
			$maxHeightThumb = 0;
			$validdays = 0;
			$subject = '';
			$emailText = '';
			$maxCharacters = '';
			$maxLines = '';
			
			
			/*******************/
			/* Update progress */
			/*******************/
			if ($_POST['saveSettings']) {
				foreach ($_POST['settingsArray'] as $settingName => $settingValue) {
					
					if ($settingName == 'emailText') {$settingValue = nl2br($settingValue);}
					
					$query = "	UPDATE
									".DBPREFIX."module_ecard_settings
								SET
									setting_value = '"  . $settingValue . "'
								WHERE
									setting_name = '"  . $settingName . "';";	
					
					if ($objResult = $objDatabase->Execute($query)) {
						$this->_objTpl->setVariable(array(
							'CONTENT_OK_MESSAGE'        => $this->strOkMessage = $_ARRAYLANG['TXT_DATA_SAVED']
						));
					}
				}
			}
			
			
			/*******************/
			/* Delete progress */
			/*******************/
			if ($_POST['deleteEcards']) {
				$unlinkOK = "";
				$unlinkFromDB = "";
				$currentDate = mktime();
				
				
				$query = "	SELECT * 
							FROM
								".DBPREFIX."module_ecard_ecards";	
					
				$objResult = $objDatabase->Execute($query);
	
				while (!$objResult->EOF) {
					if ($objResult->fields['date'] + $objResult->fields['TTL'] < $currentDate) {
						$unvalidEcardsArray[] = array('date' => $objResult->fields['date'], 'TTL' => $objResult->fields['TTL'], 'code' => $objResult->fields['code']);
					}
					$objResult->MoveNext();
				}
				
				
				if (!empty($unvalidEcardsArray)) {
					/******************************/
					/* Get the right filextension */
					/******************************/
					foreach ($unvalidEcardsArray as $value) {
						$globArray[] = array('ecardWithPath' => glob(ASCMS_ECARD_SEND_ECARDS_PATH . $value['code'] . ".*"), 'code' => $value['code']);
					}
					
					
					/********************/
					/* Delete the files */
					/********************/
					foreach ($globArray as $filename) {
						if (unlink($filename['ecardWithPath'][0])) {
							$unlinkOK = true;
						} else {
							$unlinkOK = false;
						}
						
						
						/**************************************/
						/* Delete DB records related to files */
						/**************************************/
						if ($unlinkOK == true) {
							$query = "	DELETE FROM
											".DBPREFIX."module_ecard_ecards
										WHERE
											code = '"  . $filename['code'] . "';";	
					
							if ($objResult = $objDatabase->Execute($query)) {
								$unlinkFromDB = true;
							} else {
								$unlinkFromDB = false;
							}
						}					
					}
				}
				
					
				if (($unlinkFromDB == true) && ($unlinkOK == true)) {
					$this->_objTpl->setVariable(array(
						'CONTENT_OK_MESSAGE'	=> $this->strOkMessage = $_ARRAYLANG['TXT_ECARDS_DELETED']
					));
				} else {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE'	=> $this->strErrMessage = $_ARRAYLANG['TXT_ECARDS_NOT_DELETED']
					));
				}
			}
			
			
			/********************/
			/* Display progress */
			/********************/
			$query = "	SELECT *
						FROM 
							".DBPREFIX."module_ecard_settings
						WHERE
							setting_name 
						NOT LIKE
							'motive_%'";
			
			$objResult = $objDatabase->Execute($query);
					
			while (!$objResult->EOF) {
				switch ($objResult->fields['setting_name']) {
					case 'validdays':
						$validdays = $objResult->fields['setting_value'];	
						break;
					case 'maxHeight':
						$maxHeight = $objResult->fields['setting_value'];	
						break;
					case 'maxWidth':
						$maxWidth = $objResult->fields['setting_value'];	
						break;
					case 'maxWidthThumb':
						$maxWidthThumb = $objResult->fields['setting_value'];	
						break;
					case 'maxHeightThumb':
						$maxHeightThumb = $objResult->fields['setting_value'];	
						break;
					case 'subject':
						$subject = $objResult->fields['setting_value'];	
						break;
					case 'emailText':
						$emailText = strip_tags($objResult->fields['setting_value']);	
						break;
					case 'maxCharacters':
						$maxCharacters = $objResult->fields['setting_value'];	
						break;
					case 'maxLines':
						$maxLines = $objResult->fields['setting_value'];	
						break;
				}			
				
				
				/*******************************/
				/* Initialize DATA placeholder */
				/*******************************/
				$this->_objTpl->setVariable(array(
					'VALID_DAYS'      	=> $validdays,
					'MAX_WIDTH'      	=> $maxWidth,
					'MAX_HEIGHT'      	=> $maxHeight,
					'MAX_WIDTH_THUMB'  	=> $maxWidthThumb,
					'MAX_HEIGHT_THUMB'  => $maxHeightThumb,
					'SUBJECT'      		=> $subject,
					'EMAIL_TEXT'   		=> $emailText,
					'MAX_CHARACTERS'   	=> $maxCharacters,
					'MAX_LINES'   		=> $maxLines
				));
				$objResult->MoveNext();
			}
			
			
			/*******************************/
			/* Initialize TEXT placeholder */
			/*******************************/
			$this->_objTpl->setVariable(array(
				'TXT_SAVE'								=> $_ARRAYLANG['TXT_SAVE'],
				'TXT_SETTINGS'							=> $_ARRAYLANG['TXT_SETTINGS'],
				'TXT_VALID_TIME_OF_ECARD'   		    => $_ARRAYLANG['TXT_VALID_TIME_OF_ECARD'],
				'TXT_NOTIFICATION_SUBJECT'   		    => $_ARRAYLANG['TXT_NOTIFICATION_SUBJECT'],
				'TXT_NOTIFICATION_CONTENT'				=> $_ARRAYLANG['TXT_NOTIFICATION_CONTENT'],
				'TXT_TEXTBOX'							=> $_ARRAYLANG['TXT_TEXTBOX'],
				'TXT_NUMBER_OF_CHARS_ROW'   		    => $_ARRAYLANG['TXT_NUMBER_OF_CHARS_ROW'],
				'TXT_NUMBER_OF_ROWS_MESSAGE'   		    => $_ARRAYLANG['TXT_NUMBER_OF_ROWS_MESSAGE'],
				'TXT_SPECIFICATIONS_MOTIVE'   		    => $_ARRAYLANG['TXT_SPECIFICATIONS_MOTIVE'],
				'TXT_MAX_WIDTH_OF_MOTIVE'   		    => $_ARRAYLANG['TXT_MAX_WIDTH_OF_MOTIVE'],
				'TXT_MAX_HEIGHT_OF_MOTIVE'				=> $_ARRAYLANG['TXT_MAX_HEIGHT_OF_MOTIVE'],
				'TXT_SPECIFICATIONS_THUMBNAILS'   		=> $_ARRAYLANG['TXT_SPECIFICATIONS_THUMBNAILS'],
				'TXT_MAX_WIDHT_OF_THUMBNAIL'			=> $_ARRAYLANG['TXT_MAX_WIDHT_OF_THUMBNAIL'],
				'TXT_MAX_HEIGHT_OF_THUMBNAIL'   		=> $_ARRAYLANG['TXT_MAX_HEIGHT_OF_THUMBNAIL'],
				'TXT_SENT_ECARDS'						=> $_ARRAYLANG['TXT_SENT_ECARDS'],
				'TXT_DELETE_EXPIRED_ECARDS'				=> $_ARRAYLANG['TXT_DELETE_EXPIRED_ECARDS'],
				'TXT_CLICK_HERE_DELETE_EXPIRED_ECARDS'	=> $_ARRAYLANG['TXT_CLICK_HERE_DELETE_EXPIRED_ECARDS'],
				'TXT_PLACEHOLDER'						=> $_ARRAYLANG['TXT_PLACEHOLDER'],
				'TXT_PLACEHOLDER_OVERVIEW_FRONTEND_BACKEND'	=> $_ARRAYLANG['TXT_PLACEHOLDER_OVERVIEW_FRONTEND_BACKEND'],
				'TXT_REPLACE_SALUTATION_RECEIVER'   		=> $_ARRAYLANG['TXT_REPLACE_SALUTATION_RECEIVER'],
				'TXT_REPLACE_NAME_RECEIVER'   				=> $_ARRAYLANG['TXT_REPLACE_NAME_RECEIVER'],
				'TXT_REPLACE_EMAIL_RECEIVER'   				=> $_ARRAYLANG['TXT_REPLACE_EMAIL_RECEIVER'],
				'TXT_REPLACE_NAME_SENDER'   				=> $_ARRAYLANG['TXT_REPLACE_NAME_SENDER'],
				'TXT_REPLACE_EMAIL_SENDER'   				=> $_ARRAYLANG['TXT_REPLACE_EMAIL_SENDER'],
				'TXT_REPLACE_VALID_DAYS'   					=> $_ARRAYLANG['TXT_REPLACE_VALID_DAYS'],
				'TXT_REPLACE_URL'   						=> $_ARRAYLANG['TXT_REPLACE_URL'],
				'TXT_PLACEHOLDER_OVERVIEW_FRONTEND'   		=> $_ARRAYLANG['TXT_PLACEHOLDER_OVERVIEW_FRONTEND'],
				'TXT_REPLACE_MESSAGE'   					=> $_ARRAYLANG['TXT_REPLACE_MESSAGE']
			));
		}
	
		function resizeMotive($type, $motivePath, $destinationPath) {
			global $objDatabase;
			
			//$type == 1 => resize methode for creating thumbnails
			//$type == 2 => resize methode for creating optimized motives
			
			
			/************************/
			/* Initialize variables */
			/************************/
			$motiveOriginal = "../" . $motivePath;
			$motiveFilename = strrchr($motiveOriginal, "/");
			
			$query = "	SELECT * 
						FROM
							".DBPREFIX."module_ecard_settings";
			
			$objResult = $objDatabase->Execute($query);
			
			
			/******************************************/
			/* Get resize values from settings record */
			/******************************************/
			while (!$objResult->EOF) {
				switch ($objResult->fields['setting_name']) {
					case 'maxHeight':
						$maxHeight =  $objResult->fields['setting_value'];
						break;
					case 'maxWidth':
						$maxWidth =  $objResult->fields['setting_value'];
						break;
					case 'maxHeightThumb':
						$maxHeightThumb =  $objResult->fields['setting_value'];
						break;
					case 'maxWidthThumb':
						$maxWidthThumb =  $objResult->fields['setting_value'];
						break;
				}	
				$objResult->MoveNext();
			}
			
			
			/*******************************************************************/
			/* Set dimensions for the thumbnails for frontend AND backend view */
			/*******************************************************************/
			if ($type == 1) {
				$maxHeight = $maxHeightThumb;
				$maxWidth = $maxWidthThumb;
			}
					
			
			/***********************/
			/* Get file attributes */
			/***********************/
			$size = getimagesize($motiveOriginal); 
			$width_org = $size[0]; 
			$height_org = $size[1]; 
			
			
			/**************************/
			/* Set new height / widht */
			/**************************/
			if ($width_org < $height_org) {
				$width_new = $maxWidth; 
				$height_new = intval($height_org*$width_new/$width_org); 	
			} else {
				$height_new = $maxHeight; 
				$width_new = intval($width_org*$height_new/$height_org);
			}
			
			
			/************/
			/* Resample */
			/************/
			$motiveOptimizedFile = imagecreatetruecolor($width_new, $height_new);
			
			
			/**************************/
			/* Save the new file */
			/**************************/
			if ($size[2] == 1) {
				//GIF
				$motiveOptimized = imagecreatefromgif($motiveOriginal);
				imagecopyresampled($motiveOptimizedFile, $motiveOptimized, 0, 0, 0, 0, $width_new, $height_new, $width_org, $height_org);
				imagegif($motiveOptimizedFile, $destinationPath . $motiveFilename);	
			} elseif ($size[2] == 2) {
				//JPG
				$motiveOptimized = imagecreatefromjpeg($motiveOriginal);
				imagecopyresampled($motiveOptimizedFile, $motiveOptimized, 0, 0, 0, 0, $width_new, $height_new, $width_org, $height_org);
				imagejpeg($motiveOptimizedFile, $destinationPath . $motiveFilename);	
			} elseif ($size[2] == 3) {
				//PNG
				$motiveOptimized = imagecreatefrompng($motiveOriginal);
				imagecopyresampled($motiveOptimizedFile, $motiveOptimized, 0, 0, 0, 0, $width_new, $height_new, $width_org, $height_org);
				imagepng($motiveOptimizedFile, $destinationPath . $motiveFilename);	
			}
		}
	}
?>