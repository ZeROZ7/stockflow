<?php

namespace App\Domain\PurchaseOrders;

use App\Domain\PurchaseOrders\Exceptions\InvalidPurchaseOrder;
use App\Domain\PurchaseOrders\Exceptions\InvalidStatusTransition;
use DateTimeImmutable;

/**
 * Aggregate root for the purchasing flow.
 *
 * Plain PHP on purpose: no Eloquent, no facades. Every business rule about
 * purchase orders lives here, so it can be unit tested without a database.
 */
final class PurchaseOrder
{
    /**
     * @param  list<PurchaseOrderLine>  $lines
     */
    private function __construct(
        private ?int $id,
        private readonly int $branchId,
        private readonly string $supplier,
        private PurchaseOrderStatus $status,
        private readonly array $lines,
        private readonly ?string $notes,
        private readonly int $createdBy,
        private ?int $approvedBy = null,
        private ?DateTimeImmutable $approvedAt = null,
        private ?int $receivedBy = null,
        private ?DateTimeImmutable $receivedAt = null,
    ) {}

    /**
     * Start a new purchase order in "draft" status.
     *
     * @param  list<PurchaseOrderLine>  $lines
     */
    public static function draft(
        int $branchId,
        string $supplier,
        array $lines,
        int $createdBy,
        ?string $notes = null,
    ): self {
        if (trim($supplier) === '') {
            throw InvalidPurchaseOrder::missingSupplier();
        }

        if ($lines === []) {
            throw InvalidPurchaseOrder::withoutLines();
        }

        $productIds = array_map(fn (PurchaseOrderLine $line) => $line->productId, $lines);

        if (count($productIds) !== count(array_unique($productIds))) {
            throw InvalidPurchaseOrder::duplicatedProduct();
        }

        return new self(null, $branchId, trim($supplier), PurchaseOrderStatus::Draft, array_values($lines), $notes, $createdBy);
    }

    /**
     * Rebuild an existing order from storage (used by repositories only).
     *
     * @param  list<PurchaseOrderLine>  $lines
     */
    public static function reconstitute(
        int $id,
        int $branchId,
        string $supplier,
        PurchaseOrderStatus $status,
        array $lines,
        ?string $notes,
        int $createdBy,
        ?int $approvedBy,
        ?DateTimeImmutable $approvedAt,
        ?int $receivedBy,
        ?DateTimeImmutable $receivedAt,
    ): self {
        return new self($id, $branchId, $supplier, $status, $lines, $notes, $createdBy, $approvedBy, $approvedAt, $receivedBy, $receivedAt);
    }

    public function approve(int $approverId, DateTimeImmutable $at): void
    {
        $this->transitionTo(PurchaseOrderStatus::Approved);

        $this->approvedBy = $approverId;
        $this->approvedAt = $at;
    }

    /**
     * Mark the goods as received and return the stock that must be added.
     *
     * @return list<StockMovement>
     */
    public function receive(int $receiverId, DateTimeImmutable $at): array
    {
        $this->transitionTo(PurchaseOrderStatus::Received);

        $this->receivedBy = $receiverId;
        $this->receivedAt = $at;

        return array_map(
            fn (PurchaseOrderLine $line) => new StockMovement($this->branchId, $line->productId, $line->quantity->value),
            $this->lines,
        );
    }

    public function total(): int
    {
        return array_sum(array_map(fn (PurchaseOrderLine $line) => $line->subtotal(), $this->lines));
    }

    private function transitionTo(PurchaseOrderStatus $next): void
    {
        if (! $this->status->canTransitionTo($next)) {
            throw InvalidStatusTransition::between($this->status, $next);
        }

        $this->status = $next;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function branchId(): int
    {
        return $this->branchId;
    }

    public function supplier(): string
    {
        return $this->supplier;
    }

    public function status(): PurchaseOrderStatus
    {
        return $this->status;
    }

    /** @return list<PurchaseOrderLine> */
    public function lines(): array
    {
        return $this->lines;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function createdBy(): int
    {
        return $this->createdBy;
    }

    public function approvedBy(): ?int
    {
        return $this->approvedBy;
    }

    public function approvedAt(): ?DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function receivedBy(): ?int
    {
        return $this->receivedBy;
    }

    public function receivedAt(): ?DateTimeImmutable
    {
        return $this->receivedAt;
    }
}
