<?php

namespace Tokenly\CryptoQuantity;

use Math_BigInteger as BigInt;
use Exception;

/*
* CryptoQuantity
*/
class CryptoQuantity
{

    const SATOSHI        = 100000000;
    const DECIMAL_PLACES = 8;

    protected $big_integer;
    protected $is_divisible = true;

    /**
     * Creates a new quantity from a float value
     * @param  float   $float_value   The amount as a float
     * @param  boolean $is_divisible  false for indivisible assets (default is true)
     * @return CryptoQuantity         The new CryptoQuantity object
     */
    public static function fromFloat($float_value, $is_divisible=true) {
        // left of decimal point
        $rounded_float = round($float_value);
        $big_integer_int = (new BigInt($rounded_float))->multiply(new BigInt(self::SATOSHI));

        // right of decimal point
        $float_decimal_value = round(floatval($float_value - $rounded_float) * self::SATOSHI);
        $big_integer_decimal = (new BigInt($float_decimal_value));

        // add the integer value and the decimal value
        return new self($big_integer_int->add($big_integer_decimal), $is_divisible);
    }


    /**
     * Creates an indivisible asset quantity from an integer
     * @param  integer $integer The indivisible amount
     * @return CryptoQuantity   The new CryptoQuantity object
     */
    public static function fromIndivisibleAmount($integer) {
        return new self((new BigInt($integer))->multiply(new BigInt(self::SATOSHI)), false);
    }


    /**
     * Creates an asset quantity from an integer number of satoshis
     * @param  integer $integer The amount in satoshis
     * @param  boolean $is_divisible  false for indivisible assets (default is true)
     * @return CryptoQuantity   The new CryptoQuantity object
     */
    public static function fromSatoshis($integer, $is_divisible=true) {
        return new self(new BigInt($integer), $is_divisible);
    }


    /**
     * Creates an asset quantity from an integer number of satoshis
     * @param  Math_BigInteger $big_integer   The amount as a big integer object
     * @param  boolean         $is_divisible  false for indivisible assets (default is true)
     * @return CryptoQuantity   The new CryptoQuantity object
     */
    public static function fromBigIntegerSatoshis(BigInt $big_integer, $is_divisible=true) {
        return new self($big_integer, $is_divisible);
    }

    // ------------------------------------------------------------------------

    /**
     * Returns an appropriate value for counterparty calls
     * Divisible assets return a number of satoshis represented as a string
     * Indivisible assets return a number represented as a string
     * @return string An string representation of an integer
     */
    public function getValueForCounterparty() {
        if ($this->is_divisible) {
            // divisible - convert to satoshis
            return $this->getSatoshisString();
        } else {
            // not divisible - do not use satoshis
            return $this->getIndivisibleAmountAsString();
        }
    }

    /**
     * Gets the number of satoshis represented as a string
     * @return string An string representation of an integer
     */
    public function getSatoshisString() {
        return $this->big_integer->toString();
    }

    /**
     * Gets the amount as a float
     * @return float A float representation of the value
     */
    public function getFloatValue() {
        list($quotient, $remainder) = $this->big_integer->divide(new BigInt(self::SATOSHI));
        return floatval($quotient->toString()) + floatval($remainder->toString() / self::SATOSHI);
    }


    /**
     * Passes through a call to the Math_BigInteger library and returns a new object
     * @return CryptoQuantity A new CryptoQuantity object
     */
    public function __call($method, $args) {
        $new_big_integer = call_user_func_array([$this->big_integer, $method], $args);
        return new self($new_big_integer, $this->is_divisible);
    }

    // ------------------------------------------------------------------------
    
    protected function __construct(BigInt $big_integer, $is_divisible) {
        $this->big_integer  = $big_integer;
        $this->is_divisible = $is_divisible;
    }

    /**
     * Get individisble (no decimal) amount
     * @return string An string representation of an integer
     */
    protected function getIndivisibleAmountAsString() {
        list($quotient, $remainder) = $this->big_integer->divide(new BigInt(self::SATOSHI));
        return $quotient->toString();
    }

}

