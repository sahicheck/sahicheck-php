<?php

declare(strict_types=1);

namespace SahiCheck\Ip;

use SahiCheck\Response\IpLookupResult as CanonicalIpLookupResult;

if (!class_exists(IpLookupResult::class, false)) {
    class_alias(CanonicalIpLookupResult::class, IpLookupResult::class);
}
