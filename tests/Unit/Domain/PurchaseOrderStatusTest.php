<?php

use App\Domain\PurchaseOrders\PurchaseOrderStatus as Status;

it('allows only forward transitions', function (Status $from, Status $to, bool $allowed) {
    expect($from->canTransitionTo($to))->toBe($allowed);
})->with([
    'draft -> approved' => [Status::Draft, Status::Approved, true],
    'approved -> received' => [Status::Approved, Status::Received, true],
    'draft -> received (skips approval)' => [Status::Draft, Status::Received, false],
    'approved -> draft (backwards)' => [Status::Approved, Status::Draft, false],
    'received -> approved (backwards)' => [Status::Received, Status::Approved, false],
    'received -> received (twice)' => [Status::Received, Status::Received, false],
]);
