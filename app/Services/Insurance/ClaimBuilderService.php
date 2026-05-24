<?php

namespace App\Services\Insurance;

use App\Models\Billing\Bill;
use App\Models\Insurance\Claim;
use App\Models\Insurance\ClaimItem;
use App\Models\Insurance\InsurancePolicy;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Build a Claim from a finalized Bill (SRS §5.21, prompt library Prompt 15).
 *
 * Workflow:
 *   1. Take a final or paid Bill.
 *   2. Look up an active InsurancePolicy on the patient (or accept one).
 *   3. Compute patient copay from policy.copay_percent + deductible.
 *   4. Create a Claim with status=draft, copy each BillItem to a ClaimItem.
 *   5. Caller can subsequently submit / approve / settle via Claim controller.
 */
class ClaimBuilderService
{
    public function buildFromBill(Bill $bill, ?InsurancePolicy $policy = null, array $opts = []): Claim
    {
        if (! in_array($bill->status, ['final', 'paid', 'partially_paid'], true)) {
            throw new RuntimeException('Bill must be finalized before a claim can be built.');
        }

        $policy ??= $this->resolvePolicy($bill);
        if (! $policy) {
            throw new InvalidArgumentException('Patient has no active insurance policy.');
        }
        if (! $policy->payer) {
            throw new InvalidArgumentException('Insurance policy is missing a payer.');
        }

        return DB::transaction(function () use ($bill, $policy, $opts) {
            $gross = (float) $bill->grand_total;
            $copay = round($gross * ((float) $policy->copay_percent / 100), 2);
            $deductible = (float) $policy->deductible;
            $patientShare = round(min($gross, $copay + $deductible), 2);
            $claimAmount = round($gross - $patientShare, 2);

            $claim = Claim::create([
                'organization_id' => $bill->organization_id,
                'branch_id' => $bill->branch_id,
                'payer_id' => $policy->payer_id,
                'insurance_policy_id' => $policy->id,
                'patient_id' => $bill->patient_id,
                'encounter_id' => $bill->encounter_id,
                'pre_authorization_id' => $opts['pre_authorization_id'] ?? null,
                'bill_reference' => $bill->bill_no,
                'gross_amount' => $gross,
                'patient_copay' => $patientShare,
                'claim_amount' => $claimAmount,
                'status' => 'draft',
                'claim_date' => now()->toDateString(),
                'attachments' => $opts['attachments'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Snapshot every bill item that is "insurance covered" by its
            // service catalog (manual / non-mapped items default to covered
            // unless explicitly flagged).
            foreach ($bill->items as $item) {
                $insuranceCovered = optional($item->service)->insurance_covered ?? true;
                if (! $insuranceCovered) {
                    continue;
                }
                ClaimItem::create([
                    'claim_id' => $claim->id,
                    'service_catalog_id' => $item->service_catalog_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                ]);
            }

            return $claim->fresh('items');
        });
    }

    public function submit(Claim $claim): Claim
    {
        if ($claim->status !== 'draft') {
            throw new RuntimeException('Only draft claims can be submitted.');
        }
        $claim->update([
            'status' => 'submitted',
            'submission_date' => now()->toDateString(),
        ]);
        return $claim->refresh();
    }

    public function approve(Claim $claim, float $approvedAmount): Claim
    {
        if (! in_array($claim->status, ['submitted', 'under_review'], true)) {
            throw new RuntimeException('Claim must be submitted or under review to approve.');
        }
        $claim->update([
            'status' => $approvedAmount >= $claim->claim_amount ? 'approved' : 'short_paid',
            'approved_amount' => $approvedAmount,
            'approved_by' => auth()->id(),
        ]);
        return $claim->refresh();
    }

    public function settle(Claim $claim, float $settledAmount): Claim
    {
        if (! in_array($claim->status, ['approved', 'short_paid'], true)) {
            throw new RuntimeException('Claim must be approved to settle.');
        }
        $claim->update([
            'status' => 'settled',
            'settled_amount' => $settledAmount,
            'settlement_date' => now()->toDateString(),
        ]);
        return $claim->refresh();
    }

    public function reject(Claim $claim, string $reason): Claim
    {
        $claim->update([
            'status' => 'rejected',
            'denial_reason' => $reason,
        ]);
        return $claim->refresh();
    }

    private function resolvePolicy(Bill $bill): ?InsurancePolicy
    {
        if ($bill->insurance_policy_id) {
            return InsurancePolicy::find($bill->insurance_policy_id);
        }
        return InsurancePolicy::where('patient_id', $bill->patient_id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', now()->toDateString());
            })
            ->latest('id')
            ->first();
    }
}
