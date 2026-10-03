<?php

declare(strict_types=1);

namespace SahiCheck\Email;

use SahiCheck\Response\EmailVerificationResult as CanonicalEmailVerificationResult;

if (!class_exists(EmailVerificationResult::class, false)) {
    class_alias(CanonicalEmailVerificationResult::class, EmailVerificationResult::class);
}
