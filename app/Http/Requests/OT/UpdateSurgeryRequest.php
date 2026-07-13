<?php

namespace App\Http\Requests\OT;

/**
 * Same rules as Store. Kept as a separate class so we can diverge later
 * (e.g. partial-update rules, status-aware constraints) without breaking
 * the create flow.
 */
class UpdateSurgeryRequest extends StoreSurgeryRequest
{
    // Identical rules for now — inherits everything.
}
