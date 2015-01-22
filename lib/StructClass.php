<?php
/*
 * This file is part of the Patternseek StructClass library.
 *
 * (c)2015 Tolan Blundell <tolan@patternseek.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PatternSeek\StructClass;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\Validator\Validation;

class StructClass
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
    static function fromArray( array $properties, $discardInvalidEntries=false ){
        $selfClass = get_called_class();
        $obj = new $selfClass();
        foreach( $properties as $property=>$value ){
            if( (! property_exists( get_called_class(), $property ) ) && $discardInvalidEntries ){
                continue;
            }
            $obj->$$property = $value;
        }
        return $obj;
    }

    function validate(){
        if( ! defined( "structclass-AnnotationRegistry-initilised" )){
            define( "structclass-AnnotationRegistry-initilised", true );
            AnnotationRegistry::registerLoader(function ($class) {return class_exists($class);});
        }
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        $violations = $validator->validate($this);
        if( $violations->count() > 0 ){
            $errs = 'Invalid properties in '.get_called_class()."\n";
            /** @var \Symfony\Component\Validator\ConstraintViolationInterface $issue */
            foreach( $violations as $issue ){
                $errs .=
                    $issue->getPropertyPath()
                    ." : "
                    .$issue->getMessage()
                    ." but got "
                    .$issue->getInvalidValue()
                    ." (".gettype($issue->getInvalidValue()).")\n";
            }
            throw new \Exception( $errs );
        }
    }

}