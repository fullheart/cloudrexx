<?php

namespace Cx\Modules\Shop\Model\Repository;

/**
 * PricelistsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PricelistRepository extends \Doctrine\ORM\EntityRepository
{
    public function getPricelistByCategoryAndId($category, $pricelistId)
    {
        $pricelists = $category->getPricelists();
        foreach ($pricelists as $pricelist) {
            if ($pricelist->getId() == $pricelistId) {
                return $pricelist;
            }
        }
    }

    public function getCategoryIdsByPricelist($pricelist)
    {
        $categories = $pricelist->getCategories();
        $categoryIds = array();
        foreach ($categories as $category) {
            $categoryIds[] = $category->getId();
        }

        return $categoryIds;
    }
}