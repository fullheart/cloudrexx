<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
* Main controller for LinkManager
*
* @copyright   Comvation AG
* @author      Project Team SS4U <info@comvation.com>
* @package     contrexx
* @subpackage  coremodule_linkmanager
*/

namespace Cx\Core_Modules\LinkManager\Controller;

/**
* Main controller for LinkManager
*
* @copyright   Comvation AG
* @author      Project Team SS4U <info@comvation.com>
* @package     contrexx
* @subpackage  coremodule_linkmanager
*/
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    /**
     * Get the controller classes
     * 
     * @return array array of the controller classes
     */
    public function getControllerClasses() {
        return array('Backend', 'CrawlerResult', 'Default', 'Settings', 'LinkCrawler');
    }
    
    /**
     * Returns a list of JsonAdapter class names
     * 
     * The array values might be a class name without namespace. In that case
     * the namespace \Cx\{component_type}\{component_name}\Controller is used.
     * If the array value starts with a backslash, no namespace is added.
     * 
     * Avoid calculation of anything, just return an array!
     * @return array List of ComponentController classes
     */
    public function getControllersAccessableByJson(){
        return array('JsonLink');
    }
}