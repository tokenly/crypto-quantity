<?php

namespace Tokenly\CryptoQuantity;

use Exception;
use JsonSerializable;
use Math_BigInteger as BigInt;

/*
 * CryptoQuantity
 */
class CryptoQuantity implements JsonSerializable
{

    protected static $PRECISION = 8;

    protected $big_integer;
    protected $precision;

    /**
     * Creates a new quantity from a float value
     * @param  float   $float_value   The amount as a float
     * @param  integer $precision     Number of decimal places of precision
     * @return CryptoQuantity         The new CryptoQuantity object
     */
    public static function fromFloat($float_value, $precision = null)
    {
        if ($precision === null) {
            $precision = static::$PRECISION;
        }

        // left of decimal point
        $rounded_float = intval(round($float_value));
        $big_integer_int = (new BigInt($rounded_float))->multiply(self::precisionUnitsAsBigInt(1, $precision));

        // right of decimal point
        $rounded_decimal = intval(round(floatval($float_value - $rounded_float) * pow(10, $precision)));
        $big_integer_decimal = new BigInt($rounded_decimal);

        // add the integer value and the decimal value
        return new static($big_integer_int->add($big_integer_decimal), $precision);
    }

    /**
     * Creates an asset quantity from an integer number of precisions
     * @param  integer|string $integer The amount in precisions
     * @param  integer $precision     Number of decimal places of precision
     * @return CryptoQuantity   The new CryptoQuantity object
     */
    public static function fromSatoshis($integer, $precision = null)
    {
        if ($precision === null) {
            $precision = static::$PRECISION;
        }
        return new static(new BigInt($integer), $precision);
    }

    /**
     * Creates an asset quantity from an integer number of precisions
     * @param  Math_BigInteger $big_integer   The amount as a big integer object
     * @param  integer         $precision     Number of decimal places of precision
     * @return CryptoQuantity   The new CryptoQuantity object
     */
    public static function fromBigIntegerSatoshis(BigInt $big_integer, $precision = null)
    {
        if ($precision === null) {
            $precision = static::$PRECISION;
        }
        return new static($big_integer, $precision);
    }


    /**
     * Unserialize a quantity object
     * @param  array $serialized_quantity  Serialized quantity data
     * @return CryptoQuantity The quantity class
     */
    public static function unserialize($serialized_quantity) {
        if (is_array($serialized_quantity)) {
            $json_array = $serialized_quantity;
        } else {
            $json_array = json_decode($serialized_quantity, true);
        }
        if (!is_array($json_array)) {
            throw new Exception("Invalid serialized quantity", 1);
        }

        return static::fromSatoshis($json_array['value'], $json_array['precision']);
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
     * Gets the number of precisions represented as a string
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
        list($quotient, $remainder) = $this->big_integer->divide(self::precisionUnitsAsBigInt(1, $this->precision));
        return floatval($quotient->toString()) + floatval($remainder->toString() / pow(10, $this->precision));
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
            return new static($result, $this->precision);
        }

        return $result;
    }

    /**
     * returns the precisions as a string
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
            'value' => $this->getSatoshisString(),
            'precision' => $this->precision,
        ];
    }

    // ------------------------------------------------------------------------

    protected function __construct(BigInt $big_integer, $precision)
    {
        $this->big_integer = $big_integer;
        $this->precision = $precision;
    }

    /**
     * Get individisble (no decimal) amount
     * @return string A string representation of an integer
     */
    protected function getIndivisibleAmountAsString()
    {
        list($quotient, $remainder) = $this->big_integer->divide(new BigInt($this->precision));
        return $quotient->toString();
    }

    protected static function precisionUnitsAsBigInt($int, $precision)
    {
        return new BigInt($int . str_repeat('0', $precision));
    }

}
