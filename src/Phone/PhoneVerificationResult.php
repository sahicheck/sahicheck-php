<?php

declare(strict_types=1);

namespace SahiCheck\Phone;

use SahiCheck\Response\PhoneVerificationResult as CanonicalPhoneVerificationResult;

if (!class_exists(PhoneVerificationResult::class, false)) {
    class_alias(CanonicalPhoneVerificationResult::class, PhoneVerificationResult::class);
}
