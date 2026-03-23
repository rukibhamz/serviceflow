<?php

require __DIR__ . '/../vendor/autoload.php';

// Stub missing optional packages so tests can load app classes without them.
if (! trait_exists(\Laravel\Sanctum\HasApiTokens::class)) {
    // Create the namespace if needed
    eval('namespace Laravel\Sanctum; trait HasApiTokens {}');
}
