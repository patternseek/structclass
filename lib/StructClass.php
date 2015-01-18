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
     * @param StructClass $properties
     * @return
     */
    static function fromArray( array $properties ){
        $selfClass = get_called_class();
        $obj = new $selfClass();
        foreach( $properties as $property=>$value ){
            $obj->$$property = $value;
        }
        return $obj;
    }

}