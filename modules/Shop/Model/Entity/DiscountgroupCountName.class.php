<?php

namespace Cx\Modules\Shop\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DiscountgroupCountName
 */
class DiscountgroupCountName extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $cumulative;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $discountgroupCountRates;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->discountgroupCountRates = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cumulative
     *
     * @param integer $cumulative
     * @return DiscountgroupCountName
     */
    public function setCumulative($cumulative)
    {
        $this->cumulative = $cumulative;

        return $this;
    }

    /**
     * Get cumulative
     *
     * @return integer 
     */
    public function getCumulative()
    {
        return $this->cumulative;
    }

    /**
     * Add discountgroupCountRates
     *
     * @param \Cx\Modules\Shop\Model\Entity\DiscountgroupCountRate $discountgroupCountRates
     * @return DiscountgroupCountName
     */
    public function addDiscountgroupCountRate(\Cx\Modules\Shop\Model\Entity\DiscountgroupCountRate $discountgroupCountRates)
    {
        $this->discountgroupCountRates[] = $discountgroupCountRates;

        return $this;
    }

    /**
     * Remove discountgroupCountRates
     *
     * @param \Cx\Modules\Shop\Model\Entity\DiscountgroupCountRate $discountgroupCountRates
     */
    public function removeDiscountgroupCountRate(\Cx\Modules\Shop\Model\Entity\DiscountgroupCountRate $discountgroupCountRates)
    {
        $this->discountgroupCountRates->removeElement($discountgroupCountRates);
    }

    /**
     * Get discountgroupCountRates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDiscountgroupCountRates()
    {
        return $this->discountgroupCountRates;
    }
}