<?php

namespace Tokenly\CryptoQuantity;

use JsonSerializable;
use Math_BigInteger as BigInt;

/*
 * CryptoQuantity
 */
class CryptoQuantity implements JsonSerializable
{

    protected static $SATOSHI = 100000000;

    protected $big_integer;
    protected $is_divisible = true;
    protected $satoshi;

    /**
     * Creates a new quantity from a float value
     * @param  float   $float_value   The amount as a float
     * @param  boolean $is_divisible  false for indivisible assets (default is true)
     * @return CryptoQuantity         The new CryptoQuantity object
     */
    public static function fromFloat($float_value, $is_divisible = true)
    {
        // left of decimal point
        $rounded_float = intval(round($float_value));
        $big_integer_int = (new BigInt($rounded_float))->multiply(new BigInt(static::$SATOSHI));

        // right of decimal point
        $rounded_decimal = intval(round(floatval($float_value - $rounded_float) * static::$SATOSHI));
        $big_integer_decimal = (new BigInt($rounded_decimal));

        // add the integer value and the decimal value
        return new self($big_integer_int->add($big_integer_decimal), $is_divisible, static::$SATOSHI);
    }

    /**
     * Creates an indivisible asset quantity from an integer
     * @param  integer $integer The indivisible amount
     * @return CryptoQuantity   The new CryptoQuantity object
     */
    public static function fromIndivisibleAmount($integer)
    {
        return new self((new BigInt($integer))->multiply(new BigInt(static::$SATOSHI)), false, static::$SATOSHI);
    }

    /**
     * Creates an asset quantity from an integer number of satoshis
     * @param  integer|string $integer The amount in satoshis
     * @param  boolean $is_divisible  false for indivisible assets (default is true)
     * @return CryptoQuantity   The new CryptoQuantity object
     */
    public static function fromSatoshis($integer, $is_divisible = true)
    {
        return new self(new BigInt($integer), $is_divisible, static::$SATOSHI);
    }

    /**
     * Creates an asset quantity from an integer number of satoshis
     * @param  Math_BigInteger $big_integer   The amount as a big integer object
     * @param  boolean         $is_divisible  false for indivisible assets (default is true)
     * @return CryptoQuantity   The new CryptoQuantity object
     */
    public static function fromBigIntegerSatoshis(BigInt $big_integer, $is_divisible = true)
    {
        return new self($big_integer, $is_divisible, static::$SATOSHI);
    }

    // convenience methods
    public static function satoshisToValue($integer)
    {
        return self::fromSatoshis($integer)->getFloatValue();
    }
    public static function valueToSatoshis($float)
    {
        return self::fromFloat($float)->getSatoshisString();
    }

    // ------------------------------------------------------------------------

    /**
     * Returns an appropriate value for counterparty calls
     * Divisible assets return a number of satoshis represented as a string
     * Indivisible assets return a number represented as a string
     * @return string A string representation of an integer
     */
    public function getValueForCounterparty()
    {
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
     * @return string A string representation of an integer
     */
    public function getSatoshisString()
    {
        return $this->big_integer->toString();
    }

    /**
     * Gets the amount as a float
     * @return float A float representation of the value
     */
    public function getFloatValue()
    {
        list($quotient, $remainder) = $this->big_integer->divide(new BigInt($this->satoshi));
        return floatval($quotient->toString()) + floatval($remainder->toString() / $this->satoshi);
    }

    /**
     * Passes through a call to the Math_BigInteger library and returns a new object
     * @return mixed A new CryptoQuantity object or the result of the operation
     */
    public function __call($method, $args)
    {
        $result = call_user_func_array([$this->big_integer, $method], $args);

        // wrap a Math_BigInteger result in a new CryptoQuantity
        if ($result instanceof BigInt) {
            return new self($result, $this->is_divisible, $this->satoshi);
        }

        return $result;
    }

    /**
     * Passes through accessor to the underlying Math_BigInteger instance
     * @return mixed property value
     */
    public function __get($attribute)
    {
        return $this->big_integer->$attribute;
    }

    /**
     * returns the satoshis as a string
     * @return string A string representation of an integer
     */
    public function __toString()
    {
        return $this->getSatoshisString();
    }

    /**
     * Serialize to JSON
     * @return string The string representation of this quantity
     */
    public function jsonSerialize()
    {
        return [
            'class' => get_class($this),
            'is_divisible' => $this->is_divisible,
            'value' => $this->getSatoshisString(),
        ];
    }

    // ------------------------------------------------------------------------

    protected function __construct(BigInt $big_integer, $is_divisible, $satoshi)
    {
        $this->big_integer = $big_integer;
        $this->is_divisible = $is_divisible;
        $this->satoshi = $satoshi;
    }

    /**
     * Get individisble (no decimal) amount
     * @return string A string representation of an integer
     */
    protected function getIndivisibleAmountAsString()
    {
        list($quotient, $remainder) = $this->big_integer->divide(new BigInt($this->satoshi));
        return $quotient->toString();
    }

}
