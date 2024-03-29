<?php
/*
 * This file is part of the Patternseek StructClass library.
 *
 * (c)2015 - 2021 Tolan Blundell <tolan@patternseek.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PatternSeek\StructClass;

use JsonSerializable;
use Symfony\Component\Validator\Validation;


/**
 * Class StructClass
 * @package PatternSeek\StructClass
 */
class StructClass implements JsonSerializable
{

    /**
     * Disable get of non-existent properties.
     * @param $property
     * @throws \Exception
     */
    public function __get( $property )
    {
        throw new \Exception( "Non-existent property {$property} get in " . get_class( $this ) );
    }

    /**
     * Disable set of non-existent properties.
     * @param $property
     * @param $value
     * @throws \Exception
     */
    public function __set( $property, $value )
    {
        throw new \Exception( "Non-existent property {$property} set in " . get_class( $this ) );
    }

    /**
     * Populate the StructClass's properties from an array
     * @param array $properties
     * @param bool $discardInvalidEntries If set to true, entries in $properties for which there is no corresponding class member will be discarded instead of generating an error
     * @return StructClass
     */
    public static function fromArray( array $properties, $discardInvalidEntries = false )
    {
        $selfClass = get_called_class();
        $obj = new $selfClass();
        foreach( $properties as $property=>$value ){
            if( (! property_exists( get_called_class(), $property ) ) && $discardInvalidEntries ){
                continue;
            }
            $obj->$property = $value;
        }
        return $obj;
    }

    /**
     * Convert to an array.
     * This will include any protected or private properties as well as public.
     * Normally this shouldn't be an issue as struct classes are usually
     * intended to have all members public.
     */
    public function toArray()
    {
        $ret = [ ];
        foreach ($this as $key => $value) {
            $ret[ $key ] = $value;
        }
        return $ret;
    }

    /**
     * Implement JsonSerializable
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Validate class using annotations
     * @throws \Exception
     */
    public function validate()
    {
        $validator = Validation::createValidatorBuilder()->addDefaultDoctrineAnnotationReader()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validate($this);
        $errs = '';
        if( $violations->count() > 0 ){
            $errs = 'Invalid properties in '.get_called_class()."\n";
            /** @var \Symfony\Component\Validator\ConstraintViolationInterface $issue */
            foreach( $violations as $issue ){
                $errs .=
                    $issue->getPropertyPath()
                    ." : "
                    .$issue->getMessage()
                    . " But got "
                    . var_export( $issue->getInvalidValue(), true );
            }
        }
        // Validate any properties which are StructClasses or are arrays containing StructClasses
        foreach ($this as $property) {
            if ($property instanceof StructClass) {
                try{
                    $property->validate();
                }catch( \Exception $e ){
                    $errs .= $e->getMessage();
                }
            }elseif (is_array( $property )) {
                foreach ($property as $element) {
                    if ($element instanceof StructClass) {
                        try{
                            $element->validate();
                        }catch( \Exception $e ){
                            $errs .= $e->getMessage();
                        }
                    }
                }
            }
        }
        if ($errs != '') {
            throw new \Exception( $errs );
        }
    }

}
