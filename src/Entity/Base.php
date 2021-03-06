<?php

namespace Bvarent\JobManager\Entity;

use Bva\ZendeskSync\Exception\NoMatchForMagicCall;
use Doctrine\ORM\Mapping as ORM;
use Zend\Stdlib\Extractor\ExtractionInterface;
use Zend\Stdlib\JsonSerializable;

/**
 * Blueprint for a persistable Entity.
 * * Getter/setter mechanism.
 *   - Public properties are not affected.
 *   - Non-public (protected or private) properties can be made readable and/or
 *     writable, by specifying @property docblock tags for the class.
 *     {@link http://www.phpdoc.org/docs/latest/references/phpdoc/tags/property.html property tags}
 *   - These tags are also used by IDE autocompletion and autogenerated docs.
 *   - Getter, setter, checker and unsetter methods can be intercalated. If a
 *     method like (get|set|has|uns)PropertyName exists, it is called.
 *   - Properties prefixed with an underscore (_) stay protected.
 * * Magic __call delegation system. All __call* methods are tried in order.
 * * For basic validation, use Symfony\Component\Validator\Constraint annotations.
 *
 * @todo Move the call and getter/setter systems to a trait.
 * @todo Prevent setting property which is a collection. Collection object should be gotten and then operated on.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 *
 * @ORM\MappedSuperclass
 */
abstract class Base implements JsonSerializable
{
    /**
     * @var ExtractionInterface
     */
    protected static $extractor;
    
    public static function setJsonExtractor(ExtractionInterface $extractor)
    {
        static::$extractor = $extractor;
    }
    
    /**
     * Returns a string representation of this Entity.
     *  By default: classname + id (if existent).
     * @param string $format
     * @return string
     */
    public function __toString()
    {
        $class = get_called_class();
        $str = $class;
        
        // Append ID property.
        // TODO Find out @ORM\Id properties from Doctrine's metadata.
        if (property_exists($this, 'id')) {
            $str += " #{$this->id}";
        }
        
        return $str;
    }
    
    public function jsonSerialize()
    {
        if (isset(static::$extractor)) {
            return static::$extractor->extract($this);
        }
        
        return $this;
    }

    /**
     * Cached classnames to arrays with methods to be tried by __call
     * @var Map<string, List<string>>
     */
    private static $callMethods;

    /**
     * Tries to delegate a call to an undefined method to other methods prefixed
     *  with __call.
     *
     * Those __call prefixed methods are tried in this order:
     * * Methods defined in the current class, top of the source code first.
     * * Methods from the parent class.
     *
     * Example:
     *  Some subclass 'Submarine' has a method named '__callFireTorpedoByName'
     *   and a property named 'course'.
     *  A call is placed: $someSub->setCourse($coords).
     *  fireJohn is not an existing method, so this __call method steps in.
     *  It tries the following methods:
     * * $this->__callFireTorpedoByName('fireJohn', [$coords]);
     *   which is not applicable so throws a NoMatchForMagicCall and so is skipped.
     * * parent::__callProtectedPropertyGetterSetter
     *   which sets the value of the property 'course'.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws NoMatchForMagicCall
     * @todo FIXME Endless loop and WSOD when a subclass calls a non-existing method on itself.
     */
    public function __call($method, array $args)
    {
        $class = get_called_class();

        // Find (and cache) the __call methods to be used by _call.
        if (isset(self::$callMethods[$class])) {
            $callMethods = self::$callMethods[$class];
        } else {
            // get_class_methods returns methods ordered by subclass, line number.
            // TODO Static reflection service might be faster?
            $availableMethods = get_class_methods($class);
            $callMethods = [];
            foreach ($availableMethods as $methodName) {
                if (substr($methodName, 0, 6) == '__call' && strlen($methodName) > 6) {
                    $callMethods[] = $methodName;
                }
            }
            self::$callMethods[$class] = $callMethods;
        }

        $failureMsgs = [];

        // Try all of the __call prefixed methods.
        foreach ($callMethods as $callMethod) {
            try {
                return call_user_func([$this, $callMethod], $method, $args);
            } catch (NoMatchForMagicCall $e) {
                $failureMsgs[] = $e->getMessage();
            }
        }

        // Finally throw an exception if nothing matched.
        throw new NoMatchForMagicCall("Invalid method {$method}. (Failed attempts: \n\t" . implode("\n\t", $failureMsgs) . "");
    }
    
    /**
     * __get() is utilized for reading data from inaccessible properties.
     * Redirects to ->get*()
     * @param string $name Name of the requested property.
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        
        return $this->$method();
    }
    
    /**
     * __set() is run when writing data to inaccessible properties.
     * Redirects to ->set*($value)
     * @param string $name Name of the requested property.
     */
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);
        
        $this->$method($value);
    }
    
    /**
     * __isset() is triggered by calling isset() or empty() on inaccessible properties.
     * Redirects to ->has*()
     * @param string $name Name of the requested property.
     * @return boolean
     */
    public function __isset($name)
    {
        $method = 'has' . ucfirst($name);
        
        return $this->$method();
    }
    
    /**
     * __unset() is invoked when unset() is used on inaccessible properties.
     * Redirects to ->uns*()
     * @param string $name Name of the requested property.
     */
    public function __unset($name)
    {
        $method = 'uns' . ucfirst($name);
        
        $this->$method();
    }

    /**
     * Getter/setter for protected properties.
     * See {@see Base} for a description of the mechanism.
     * @param string $methodName Accepts get..., set..., has... and uns...
     * @param array $args If the methods is set..., the first arg is the input.
     * @throws NoMatchForMagicCall
     * @return mixed In case of set... and uns... return $this for chaining.
     */
    protected function __callDefaultMagic($methodName, array $args)
    {
        // Split method name into method and key.
        $property = lcfirst(substr($methodName, 3));
        $operation = substr($methodName, 0, 3);

        // Call the default handler.
        switch ($operation) {
            case 'get' :
                return $this->defaultMagicGet($property);

            case 'set' :
                $value = isset($args[0]) ? $args[0] : null;
                return $this->defaultMagicSet($property, $value);

            case 'uns' :
                return $this->defaultMagicUns($property);

            case 'has' :
                return isset($this->$property);
                
            default :
                throw new NoMatchForMagicCall(__METHOD__ . " -> Unknown operation '{$operation}'.");
        }
    }
    
    /**
     * Default implementation of ->get*().
     * @see __get
     * @param string $property The name of the property to get.
     * @return mixed
     * @throws NoMatchForMagicCall
     */
    protected function defaultMagicGet($property)
    {
        if (!$this->isPropertyReadable($property)) {
            throw new NoMatchForMagicCall(__METHOD__ . " -> can't get property '{$property}'.");
        }
        return $this->$property;
    }
    
    /**
     * Default implementation of ->set*($value).
     * @see __set
     * @param string $property The name of the property to set.
     * @param string $value The value to set.
     * @return static chain
     * @throws NoMatchForMagicCall
     */
    protected function defaultMagicSet($property, $value)
    {
        if (!$this->isPropertyWritable($property)) {
            throw new NoMatchForMagicCall(__METHOD__ . " -> can't set property '{$property}'.");
        }
        $this->$property = $value;
        
        return $this;
    }
    
    /**
     * Default implementation of ->has*().
     * @see __isset
     * Determines if a property exists and is not null.
     * @param string $property The name of the property to test.
     * @return boolean
     */
    protected function defaultMagicHas($property)
    {
        return $this->isPropertyReadable($property) && isset($this->$property);
    }
    
    /**
     * Default implementation of ->uns*().
     * @see __unset
     * Determines if a property exists and is not null.
     * @param string $property The name of the property to unset.
     * @return static chain
     * @throws NoMatchForMagicCall
     */
    protected function defaultMagicUns($property)
    {
        if (!$this->isPropertyWritable($property)) {
            throw new NoMatchForMagicCall(__METHOD__ . " -> can't unset property '{$property}'.");
        }
        unset($this->$property);
        
        return $this;
    }
    
    /**
     * Determines if some property of this class should be publically readable.
     * @param string $property
     * @return boolean
     */
    protected function isPropertyReadable($property)
    {
        // Protected properties, starting with an _, are forbidden terrain.
        // TODO Read @property tags from the class to determine readability.
        if (substr($property, 0, 1) == "_" || !property_exists($this, $property)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Determines if some property of this class should be publically writable.
     * @param string $property
     * @return boolean
     */
    protected function isPropertyWritable($property)
    {
        // TODO Read @property tags from the class to determine writability.
        return $this->isPropertyReadable($property);
    }
    
    /**
     * Gives this entity's properties their initial values. I.e. new object
     * instances.
     * @todo Automatically init properties of type Collection as new ArrayCollection.
     */
    protected function initProperties()
    {
    }

    public function __construct()
    {
        $this->initProperties();
    }
}
