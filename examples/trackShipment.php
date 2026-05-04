<?php

declare(strict_types=1);

use Sonnenglas\MyDHL\MyDHL;

require __DIR__ . '/../vendor/autoload.php';

$myDhl = new MyDHL('username', 'password', testMode: true);

$tracked = $myDhl->getTrackingService()->track('9356579890');

if ($tracked === null) {
    echo "Shipment not found\n";
    exit(0);
}

printf(
    "%s — %s (product %s)\n",
    $tracked->shipmentTrackingNumber,
    $tracked->status,
    $tracked->productCode ?? '?',
);

foreach ($tracked->events as $event) {
    printf(
        "  %s %s — %s%s\n",
        $event->date,
        $event->time,
        $event->description,
        $event->signedBy !== null && $event->signedBy !== '' ? " (signed by {$event->signedBy})" : '',
    );
}
