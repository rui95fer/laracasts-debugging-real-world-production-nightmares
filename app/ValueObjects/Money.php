<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Money Value Object
 * 
 * Episode 10: The Silent Money Bug
 * 
 * This is the CORRECT way to handle money in PHP.
 * All values are stored internally as cents (integers).
 * This avoids floating-point precision issues.
 */
class Money implements JsonSerializable
{
    private int $cents;

    /**
     * Create Money from cents
     */
    public function __construct(int $cents)
    {
        $this->cents = $cents;
    }

    /**
     * Create Money from dollars (converts to cents internally)
     * 
     * Note: This is the only place where float conversion happens,
     * and it's immediately rounded to the nearest cent.
     */
    public static function fromDollars(float $dollars): self
    {
        return new self((int) round($dollars * 100));
    }

    /**
     * Create Money from cents
     */
    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    /**
     * Create zero money
     */
    public static function zero(): self
    {
        return new self(0);
    }

    /**
     * Add two Money values
     */
    public function add(Money $other): self
    {
        return new self($this->cents + $other->cents);
    }

    /**
     * Subtract Money
     */
    public function subtract(Money $other): self
    {
        return new self($this->cents - $other->cents);
    }

    /**
     * Multiply by a scalar (e.g., quantity)
     */
    public function multiply(float $multiplier): self
    {
        return new self((int) round($this->cents * $multiplier));
    }

    /**
     * Divide by a scalar
     */
    public function divide(float $divisor): self
    {
        if ($divisor == 0) {
            throw new InvalidArgumentException('Cannot divide by zero');
        }
        
        return new self((int) round($this->cents / $divisor));
    }

    /**
     * Calculate percentage (e.g., tax)
     * 
     * @param float $percentage The percentage as decimal (0.08 = 8%)
     */
    public function percentage(float $percentage): self
    {
        return new self((int) round($this->cents * $percentage));
    }

    /**
     * Check if equals another Money
     */
    public function equals(Money $other): bool
    {
        return $this->cents === $other->cents;
    }

    /**
     * Check if greater than
     */
    public function greaterThan(Money $other): bool
    {
        return $this->cents > $other->cents;
    }

    /**
     * Check if less than
     */
    public function lessThan(Money $other): bool
    {
        return $this->cents < $other->cents;
    }

    /**
     * Check if zero or negative
     */
    public function isZeroOrLess(): bool
    {
        return $this->cents <= 0;
    }

    /**
     * Get value in cents
     */
    public function getCents(): int
    {
        return $this->cents;
    }

    /**
     * Get value in dollars (for display only!)
     * 
     * WARNING: Do not use this for calculations!
     */
    public function toDollars(): float
    {
        return $this->cents / 100;
    }

    /**
     * Format for display
     */
    public function format(string $symbol = '$'): string
    {
        return $symbol . number_format($this->toDollars(), 2);
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * JSON serialization
     */
    public function jsonSerialize(): array
    {
        return [
            'cents' => $this->cents,
            'dollars' => $this->toDollars(),
            'formatted' => $this->format(),
        ];
    }
}
