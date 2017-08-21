<?php

namespace Cx\Model\Proxies\__CG__\Cx\Core\User\Model\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class Group extends \Cx\Core\User\Model\Entity\Group implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = array();



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }

    /**
     * {@inheritDoc}
     * @param string $name
     */
    public function __get($name)
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__get', array($name));

        return parent::__get($name);
    }





    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return array('__isInitialized__', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'groupId', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'groupName', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'groupDescription', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'isActive', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'type', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'homepage', 'toolbar', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'user', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'accessId2', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'accessId', 'validators', 'virtual');
        }

        return array('__isInitialized__', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'groupId', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'groupName', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'groupDescription', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'isActive', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'type', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'homepage', 'toolbar', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'user', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'accessId2', '' . "\0" . 'Cx\\Core\\User\\Model\\Entity\\Group' . "\0" . 'accessId', 'validators', 'virtual');
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (Group $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', array());
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', array());
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getGroupId()
    {
        if ($this->__isInitialized__ === false) {
            return (int)  parent::getGroupId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getGroupId', array());

        return parent::getGroupId();
    }

    /**
     * {@inheritDoc}
     */
    public function setGroupName($groupName)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setGroupName', array($groupName));

        return parent::setGroupName($groupName);
    }

    /**
     * {@inheritDoc}
     */
    public function getGroupName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getGroupName', array());

        return parent::getGroupName();
    }

    /**
     * {@inheritDoc}
     */
    public function setGroupDescription($groupDescription)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setGroupDescription', array($groupDescription));

        return parent::setGroupDescription($groupDescription);
    }

    /**
     * {@inheritDoc}
     */
    public function getGroupDescription()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getGroupDescription', array());

        return parent::getGroupDescription();
    }

    /**
     * {@inheritDoc}
     */
    public function setIsActive($isActive)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIsActive', array($isActive));

        return parent::setIsActive($isActive);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsActive()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIsActive', array());

        return parent::getIsActive();
    }

    /**
     * {@inheritDoc}
     */
    public function setType($type)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setType', array($type));

        return parent::setType($type);
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getType', array());

        return parent::getType();
    }

    /**
     * {@inheritDoc}
     */
    public function setHomepage($homepage)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setHomepage', array($homepage));

        return parent::setHomepage($homepage);
    }

    /**
     * {@inheritDoc}
     */
    public function getHomepage()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getHomepage', array());

        return parent::getHomepage();
    }

    /**
     * {@inheritDoc}
     */
    public function setToolbar($toolbar)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setToolbar', array($toolbar));

        return parent::setToolbar($toolbar);
    }

    /**
     * {@inheritDoc}
     */
    public function getToolbar()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getToolbar', array());

        return parent::getToolbar();
    }

    /**
     * {@inheritDoc}
     */
    public function addUser(\Cx\Core\User\Model\Entity\User $user)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addUser', array($user));

        return parent::addUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function removeUser(\Cx\Core\User\Model\Entity\User $user)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'removeUser', array($user));

        return parent::removeUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function getUser()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUser', array());

        return parent::getUser();
    }

    /**
     * {@inheritDoc}
     */
    public function addAccessId2(\Cx\Core_Modules\Access\Model\Entity\AccessId $accessId2)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addAccessId2', array($accessId2));

        return parent::addAccessId2($accessId2);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessId2()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAccessId2', array());

        return parent::getAccessId2();
    }

    /**
     * {@inheritDoc}
     */
    public function addAccessId(\Cx\Core_Modules\Access\Model\Entity\AccessId $accessId)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addAccessId', array($accessId));

        return parent::addAccessId($accessId);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessId()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAccessId', array());

        return parent::getAccessId();
    }

    /**
     * {@inheritDoc}
     */
    public function getComponentController()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getComponentController', array());

        return parent::getComponentController();
    }

    /**
     * {@inheritDoc}
     */
    public function setVirtual($virtual)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setVirtual', array($virtual));

        return parent::setVirtual($virtual);
    }

    /**
     * {@inheritDoc}
     */
    public function isVirtual()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isVirtual', array());

        return parent::isVirtual();
    }

    /**
     * {@inheritDoc}
     */
    public function validate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'validate', array());

        return parent::validate();
    }

    /**
     * {@inheritDoc}
     */
    public function __call($methodName, $arguments)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, '__call', array($methodName, $arguments));

        return parent::__call($methodName, $arguments);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, '__toString', array());

        return parent::__toString();
    }

}
