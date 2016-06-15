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
 * Host
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_sync
 */

namespace Cx\Core_Modules\Sync\Model\Entity;

/**
 * Host
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_sync
 */
class Host extends \Cx\Model\Base\EntityBase
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $host
     */
    protected $host;

    /**
     * @var boolean $active
     */
    protected $active;

    /**
     * @var string $apiKey
     */
    protected $apiKey;

    /**
     * @var integer $apiVersion
     */
    protected $apiVersion;

    /**
     * @var string $urlTemplate
     */
    protected $urlTemplate;

    /**
     * @var Cx\Core_Modules\Sync\Model\Entity\HostEntity
     */
    protected $hostEntities;
    
    /**
     * @var string Default template for URI
     */
    protected $defaultUrlTemplate = '[[HOST]]/api/sync/[[API_VERSION]]/json/[[DATA_SOURCE]]/[[INDEX_DATA]]?apikey=[[API_KEY]]';

    public function __construct()
    {
        $this->hostEntities = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set host
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Get host
     *
     * @return string $host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set active
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return boolean $active
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set apiKey
     *
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get apiKey
     *
     * @return string $apiKey
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set apiVersion
     *
     * @param integer $apiVersion
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    /**
     * Get apiVersion
     *
     * @return integer $apiVersion
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * Set urlTemplate
     *
     * @param string $urlTemplate
     */
    public function setUrlTemplate($urlTemplate)
    {
        if ($urlTemplate == $this->defaultUrlTemplate) {
            $urlTemplate = null;
        }
        $this->urlTemplate = $urlTemplate;
    }

    /**
     * Get urlTemplate
     *
     * @return string $urlTemplate
     */
    public function getUrlTemplate()
    {
        if (empty($this->urlTemplate)) {
            return $this->defaultUrlTemplate;
        }
        return $this->urlTemplate;
    }

    /**
     * Add hostEntity
     *
     * @param Cx\Core_Modules\Sync\Model\Entity\HostEntity $hostEntity
     */
    public function addHostEntity(\Cx\Core_Modules\Sync\Model\Entity\HostEntity $hostEntity)
    {
        $this->hostEntities[] = $hostEntity;
    }

    /**
     * Get hostEntities
     *
     * @return Doctrine\Common\Collections\Collection $hostEntities
     */
    public function getHostEntities()
    {
        return $this->hostEntities;
    }

    /**
     * Set hostEntities
     *
     * @param array $hostEntities
     */
    public function setHostEntities($hostEntities)
    {
        $this->hostEntities = $hostEntities;
    }
    
    /**
     * Returns the URL for pushing data to this host
     * @param string $dataSourceName DataSource name to get the URL for
     * @param array $entityIndexData (optional) Field=>Value-type array with entity index data
     * @return string URL to push to
     */
    public function getToUri($dataSourceName, $entityIndexData = array()) {
        $indexData = implode('/', $entityIndexData);
        
        //<domain>(/<offset>)/api/sync/<apiVersion>/<outputModule>/<dataSource>/<parameters>[(?apikey=<apikey>(&<options>))|?<options>]
        //<domain>(/<offset>)/api/sync/<apiVersion>/<outputModule>/[[DATA_SOURCE]]/[[INDEX_DATA]][(?apikey=[[API_KEY]](&<options>))|?<options>]
        $uri = $this->getUrlTemplate();
        $uri = str_replace('[[HOST]]', $this->getHost(), $uri);
        $uri = str_replace('[[API_VERSION]]', $this->getApiVersion(), $uri);
        $uri = str_replace('[[DATA_SOURCE]]', $dataSourceName, $uri);
        $uri = str_replace('[[API_KEY]]', $this->getApiKey(), $uri);
        $uri = str_replace('[[INDEX_DATA]]', $indexData, $uri);
        return $uri;
    }
}
