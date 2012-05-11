<?php

namespace Cx\Model\ContentManager\Repository;

use Doctrine\Common\Util\Debug as DoctrineDebug;
use Doctrine\ORM\EntityRepository,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Query\Expr;

class PageRepositoryException extends \Exception {};
class TranslateException extends \Exception {};

class PageRepository extends EntityRepository {
    const SEARCH_MODE_PAGES_ONLY = 1;
    const SEARCH_MODE_ALIAS_ONLY = 2;
    const SEARCH_MODE_ALL = 3;
    const DataProperty = '__data';
    protected $em = null;
    private $virtualPages = array();

    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->em = $em;
    }

    /**
     * An array of pages sorted by their langID for specified module and cmd.
     *
     * @param string $module
     * @param string $cmd optional
     *
     * @return array ( langId => Page )
     */
    public function getFromModuleCmdByLang($module, $cmd = null) {
        $crit = array( 'module' => $module );
        if($cmd)
            $crit['cmd'] = $cmd;

        $pages = $this->findBy($crit);
        $ret = array();

        foreach($pages as $page) {
            $ret[$page->getLang()] = $page;
        }

        return $ret;
    }

    public function addVirtualPage($virtualPage, $afterSlug = '') {
        $virtualPage->setVirtual(true);
        if (!$virtualPage->getLang()) {
            $virtualPage->setLang(FRONTEND_LANG_ID);
        }
        $this->virtualPages[] = array(
            'page'      => $virtualPage,
            'afterSlug' => $afterSlug,
        );
    }
    
    protected function addVirtualTree($tree, $lang, $rootNodeLvl, $rootPath) {
        $tree = $this->addVirtualTreeLvl($tree, $lang, $rootNodeLvl, $rootPath);
        foreach ($tree as $slug=>$data) {
            if ($slug == '__data') {
                continue;
            }
            if ($tree[$slug]['__data']['page']->isVirtual()) {
                continue;
            }
            $tree[$slug] = $this->addVirtualTreeLvl($data, $lang, $rootNodeLvl, $tree[$slug]['__data']['page']->getPath());
            // Recursion for the tree
            $tree[$slug] = $this->addVirtualTree($data, $lang, $rootNodeLvl + 1, $tree[$slug]['__data']['page']->getPath());
        }
        return $tree;
    }
    
    protected function addVirtualTreeLvl($tree, $lang, $rootNodeLvl, $rootPath) {
        foreach ($this->virtualPages as $virtualPage) {
            $page = $virtualPage['page'];
            $node = $page->getNode();
            if (count(explode('/', $page->getPath())) - 2 != $rootNodeLvl ||
                    // Only add pages within path of currently parsed node
                    substr($page->getPath().'/', 0, strlen($rootPath.'/')) != $rootPath.'/') {
                continue;
            }
            $afterSlug = $virtualPage['afterSlug'];
            
            if (!empty($afterSlug)) {
                $position = array_search($afterSlug, array_keys($tree)) + 1;
                $head = array_splice($tree, 0, $position);
                $insert[$page->getSlug()] = array(
                    '__data' => array(
                        'lang' => array($lang),
                        'page' => $page,
                        'node' => $node,
                    ),
                );
                $tree = array_merge($head, $insert, $tree);
            } else {
                $tree[$page->getSlug()] = array(
                    '__data' => array(
                        'lang' => array($lang),
                        'page' => $page,
                        'node' => $node,
                    ),
                );
            }
            // Recursion for virtual subpages of a virtual page
            $tree[$page->getSlug()] = $this->addVirtualTreeLvl($tree[$page->getSlug()], $lang, $rootNodeLvl + 1, $page->getPath());
        }
        
        return $tree;
    }

    /**
     * Get a tree of all Nodes with their Pages assigned.
     *
     * @todo there has once been a $lang param here, but fetching only a certain language fills 
     *       the pages collection on all nodes with only those fetched pages. this means calling
     *       getPages() later on said nodes will yield a collection containing only a subset of
     *       all pages linked to the node. now, we're fetching all pages and sorting those not
     *       matching the desired language out in @link getTreeByTitle() to prevent the
     *       associations from being destroyed.
     *       naturally, this generates big overhead. this strategy should be rethought.
     * @todo $titlesOnly param is not respected - huge overhead.
     * @param Node $rootNode limit query to subtree.
     * @param boolean $titlesOnly fetch titles only. You may want to use @link getTreeByTitle()
     * @return array
     */
    public function getTree($rootNode = null, $titlesOnly = false, $search_mode = self::SEARCH_MODE_PAGES_ONLY) {
        $repo = $this->em->getRepository('Cx\Model\ContentManager\Node');
        $qb = $this->em->createQueryBuilder();

        $joinConditionType = null;
        $joinCondition = null;

        $qb->addSelect('p');
        
        //join the pages
        $qb->leftJoin('node.pages', 'p', $joinConditionType, $joinCondition);
        $qb->where($qb->expr()->gt('node.lvl', 0)); //exclude root node
        switch ($search_mode) {
            case self::SEARCH_MODE_ALIAS_ONLY:
                $qb->andWhere("p.type = 'alias'"); //exclude non alias nodes
                continue;
            case self::SEARCH_MODE_ALL:
                continue;
            case self::SEARCH_MODE_PAGES_ONLY:
            default:
                $qb->andWhere("p.type != 'alias'"); //exclude alias nodes
                continue;
        }
        
        //get all nodes
        if (is_object($rootNode) && !$rootNode->getId()) {
            $tree = array();
        } else {
            $tree = $repo->children($rootNode, false, 'lft', 'ASC', $qb);
        }

        return $tree;
    }
    
    /**
     * Get a tree mapping titles to Page, Node and language.
     *
     * @see getTree()
     * @return array ( title => array( '__data' => array(lang => langId, page =>), child1Title => array, child2Title => array, ... ) ) recursively array-mapped tree.
     */
    public function getTreeByTitle($rootNode = null, $lang = null, $titlesOnly = false, $useSlugsAsTitle=false, $search_mode = self::SEARCH_MODE_PAGES_ONLY) {
        $tree = $this->getTree($rootNode, true, $search_mode);

        $result = array();

        $isRootQuery = !$rootNode || ( isset($rootNode) && $rootNode->getLvl() == 0 );

        for($i = 0; $i < count($tree); $i++) {
            $lang2Arr = null;
            $rightLevel = false;
            $node = $tree[$i];
            if($isRootQuery)
                $rightLevel = $node->getLvl() == 1;
            else
                $rightLevel = $node->getLvl() == $rootNode->getLvl() + 1;

            if($rightLevel)
                $i = $this->treeByTitle($tree, $i, $result, $useSlugsAsTitle, $lang2Arr, $lang);
            else {
                $i++;
            }
        }

        if (!empty($this->virtualPages)) {
            $rootNodeLvl = $rootNode ? $rootNode->getLvl() : 0;
            $rootPath = $rootNode ? $rootNode->getPage($lang) ? $rootNode->getPage($lang)->getPath() : '' : '';
            $result = $this->addVirtualTree($result, $lang, $rootNodeLvl, $rootPath);
        }

        return $result;
    }

    protected function treeByTitle(&$nodes, $startIndex, &$result, $useSlugsAsTitle=false, &$lang2Arr = null, $lang = null) {
        //first node we treat
        $index = $startIndex;
        $node = $nodes[$index];
        $nodeCount = count($nodes);

        //only treat nodes on this level and higher
        $minLevel = $node->getLvl();

        $thisLevelLang2Arr = array();
        do {
            if($node->getLvl() == $minLevel) {
                $this->treeByTitlePages($nodes[$index], $result, $useSlugsAsTitle, $lang2Arr, $lang, $thisLevelLang2Arr);
                $index++;
            }
            else {
                $index = $this->treeByTitle($nodes, $index, $result, $useSlugsAsTitle, $thisLevelLang2Arr, $lang);
            }

            if($index == $nodeCount) //we traversed all nodes
                break;
            $node = $nodes[$index];
        }
        while($node->getLvl() >= $minLevel);

        return $index;
    }

    protected function treeByTitlePages($node, &$result, $useSlugsAsTitle, &$lang2Arr, $lang, &$thisLevelLang2Arr) {
        //get titles of all Pages linked to this Node
        $pages = null;

        if(!$lang) {
            $pages = $node->getPages();
        }
        else {
            $pages = array();
            $page = $node->getPage($lang);
            if($page)
                $pages = array($page);
        }

        foreach($pages as $page) {
            $title = $page->getTitle();

            if($useSlugsAsTitle)
                $title = $page->getSlug();

            $lang = $page->getLang();

            if($lang2Arr) //this won't be set for the first node
                $target = &$lang2Arr[$lang];
            else
                $target = &$result;

            if(isset($target[$title])) { //another language's Page has the same title
                //add the language
                $target[$title]['__data']['lang'][] = $lang;
            }
            else {
                $target[$title] = array();
                $target[$title]['__data'] = array(
                                                  'lang' => array($lang),
                                                  'page' => $page,
                                                  'node' => $node,
                                                  );
            }
            //remember mapping for recursion
            $thisLevelLang2Arr[$lang] = &$target[$title];
        }
    }

    /**
     * Tries to find the path's Page.
     *
     * @param string $path e.g. Hello/APage/AModuleObject
     * @param Node $root
     * @param int $lang
     * @param boolean $exact if true, returns null on partially matched path
     * @return array (
     *     matchedPath => string (e.g. 'Hello/APage/'),
     *     unmatchedPath => string (e.g. 'AModuleObject') | null,
     *     node => Node,
     *     lang => array (the langIds where this matches),
     *     [ pages = array ( all pages ) ] #langId = null only
     *     [ page => Page ] #langId != null only
     * )
     */
    public function getPagesAtPath($path, $root = null, $lang = null, $exact = false, $search_mode = self::SEARCH_MODE_PAGES_ONLY) {
        $tree = $this->getTreeByTitle($root, $lang, true, true, $search_mode);

        //this is a mock strategy. if we use this method, it should be rewritten to use bottom up
        $pathParts = explode('/', $path);
        $matchedLen = 0;
        $treePointer = &$tree;

        foreach($pathParts as $part) {
            if(isset($treePointer[$part])) {
                $treePointer = &$treePointer[$part];
                $matchedLen += strlen($part);
                if('/' == substr($path,$matchedLen,1))
                    $matchedLen++;
            }
            else {
                if($exact)
                    return null;
                break;
            }
        }

        //no level matched
        if($matchedLen == 0)
            return null;

        $unmatchedPath = substr($path, $matchedLen);
        if(!$unmatchedPath) { //beautify the to empty string
            $unmatchedPath = '';
        }

        $result = array(
            'matchedPath' => substr($path, 0, $matchedLen),
            'unmatchedPath' => $unmatchedPath
        );
        if(!$lang) {
            $result['pages'] = $treePointer['__data']['node']->getPagesByLang();
            $result['lang'] = $treePointer['__data']['lang'];
        }
        else {
            $page = $treePointer['__data']['node']->getPagesByLang();
            $page = $page[$lang];
            $result['page'] = $page;
        }

        return $result;
    }

    /**
     * Get a pages' path. Alias for $page->getPath() for compatibility reasons
     * For compatibility reasons, this path won't start with a slash!
     * @todo remove this method
     *
     * @param \Cx\Model\ContentManager\Page $page
     * @return string path, e.g. 'This/Is/It'
     */
    public function getPath($page) {
        return substr($page->getPath(), 1) . '/';
    }
    
    /**
     * Returns an array with the page translations of the given page id.
     * 
     * @param  int  $pageId
     * @param  int  $historyId  If the page does not exist, we need the history id to revert them.
     */
    public function getPageTranslations($pageId, $historyId) {
        $pages = array();
        $pageTranslations = array();
        
        $currentPage = $this->findOneById($pageId);
        // If page is deleted
        if (!is_object($currentPage)) {
            $currentPage = new \Cx\Model\ContentManager\Page();
            $currentPage->setId($pageId);
            $logRepo = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry');
            $logRepo->revert($currentPage, $historyId);
            
            $logs = $logRepo->getLogsByAction('remove');
            foreach ($logs as $log) {
                $page = new \Cx\Model\ContentManager\Page();
                $page->setId($log->getObjectId());
                $logRepo->revert($page, $log->getVersion() - 1);
                if ($page->getNodeIdShadowed() == $currentPage->getNodeIdShadowed()) {
                    $pages[] = $page;
                }
            }
        } else { // Page exists
            $pages = $this->findByNodeIdShadowed($currentPage->getNodeIdShadowed());
        }
        
        foreach ($pages as $page) {
            $pageTranslations[$page->getLang()] = \FWLanguage::getLanguageCodeById($page->getLang());
        }
        
        return $pageTranslations;
    }

    /**
     * Searches the content and returns an array that is built as needed by the search module.
     *
     * Please do not use this anywhere else, write a search method with proper results instead. Ideally, this
     * method would then be invoked by searchResultsForSearchModule().
     *
     * @param string $string the string to match against.
     * @return array (
     *     'Score' => int
     *     'Title' => string
     *     'Content' => string
     *     'Link' => string
     * )
     */
    public function searchResultsForSearchModule($string) {
        if($string == '')
            return array();

//TODO: use MATCH AGAINST for score
//      Doctrine can be extended as mentioned in http://groups.google.com/group/doctrine-user/browse_thread/thread/69d1f293e8000a27
//TODO: shorten content in query rather than in php

        $qb = $this->em->createQueryBuilder();
        $qb->add('select', 'p')
            ->add('from', 'Cx\Model\ContentManager\Page p')
            ->add('where',
                $qb->expr()->andx(
                    $qb->expr()->eq('p.lang', FRONTEND_LANG_ID),
                    $qb->expr()->orx(
                        $qb->expr()->like('p.content', ':searchString'),
                        $qb->expr()->like('p.title', ':searchString')
                    )
                )
            );

        $qb->setParameter('searchString', '%'.$string.'%');

        $pages = $qb->getQuery()->getResult();

        $config = \Env::get('config');

        $results = array();

        foreach($pages as $page) {
            if (!$page->isActive()) {
                continue;
            }

            if (   $config['searchVisibleContentOnly'] == 'on'
                && !$page->isVisible()
            ) {
                continue;
            }

            $results[] = array(
                'Score' => 100,
                'Title' => $page->getTitle(),
                'Content' => substr($page->getTitle(),0, $config['searchDescriptionLength']),
//TODO: awww this is sooo costly. @see getPath()
                'Link' => $this->getPath($page)
            );
        }

        return $results;
    }

    /**
     * Returns true if the page selected by its language, module name (section)
     * and optional cmd parameters exists
     * @param   integer     $lang       The language ID
     * @param   string      $module     The module (aka section) name
     * @param   string      $cmd        The optional cmd parameter value
     * @return  boolean                 True if the page exists, false
     *                                  otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @since   3.0.0
     * @internal    Required by the Shop module
     */
    public function existsModuleCmd($lang, $module, $cmd=null)
    {
        $crit = array(
            'module' => $module,
            'lang' => $lang,
        );
        if (isset($cmd)) $crit['cmd'] = $cmd;
        return (boolean)$this->findOneBy($crit);
    }

    public function getLastModifiedPages($from, $count) {
        $query = $this->em->createQuery("
            select p from Cx\Model\ContentManager\Page p 
                 order by p.updatedAt asc
        ");
        $query->setFirstResult($from);
        $query->setMaxResults($count);

        return $query->getResult();
    }
    
    public function getHistory($from=0, $count=30) {
        $logRepo = $this->em->getRepository('Cx\Model\ContentManager\HistoryLogEntry');
        $logs = $logRepo->getLogHistory('Cx\Model\ContentManager\Page', $from, $count);
        $ids = array();
        foreach($logs as $log) {
            $ids[] = $logs->getId();
        }

        $pages = $this->find($ids);
        return $pages;       
    }
}