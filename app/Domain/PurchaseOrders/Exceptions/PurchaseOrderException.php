<?php

namespace App\Domain\PurchaseOrders\Exceptions;

use DomainException;

/**
 * Base type for every business rule violation in the purchasing module.
 */
abstract class PurchaseOrderException extends DomainException {}
