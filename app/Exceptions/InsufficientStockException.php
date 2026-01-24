<?php

namespace App\Exceptions;

use Exception;

/**
 * Insufficient Stock Exception
 * 
 * Episode 4: Race Conditions in Inventory
 * 
 * Thrown when checkout fails due to insufficient stock.
 * The CORRECT implementation uses this with database locking
 * to prevent race conditions.
 */
class InsufficientStockException extends Exception
{
    protected string $productName;
    protected int $requestedQuantity;
    protected int $availableQuantity;

    public function __construct(
        string $productName,
        int $requestedQuantity,
        int $availableQuantity
    ) {
        $this->productName = $productName;
        $this->requestedQuantity = $requestedQuantity;
        $this->availableQuantity = $availableQuantity;

        $message = "Insufficient stock for {$productName}. " .
                   "Requested: {$requestedQuantity}, Available: {$availableQuantity}";

        parent::__construct($message);
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getRequestedQuantity(): int
    {
        return $this->requestedQuantity;
    }

    public function getAvailableQuantity(): int
    {
        return $this->availableQuantity;
    }
}
