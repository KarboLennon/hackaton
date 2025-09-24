<?php

namespace App\Services;

use App\Models\User;
use App\Models\PointLedger;
use Illuminate\Support\Str;

class ReferralService
{
    // Berapa poin hadiah (silakan ubah sesuai kebutuhan)
    private int $signupBonus     = 5;   // referrer dapat saat invitee daftar
    private int $activationBonus = 15;  // referrer dapat saat invitee "aktif" (mis. 1st approved submission)

    /**
     * Pastikan user punya referral_code unik. Aman dipanggil berkali-kali.
     */
    public function ensureReferralCode(User $user): void
    {
        if (!empty($user->referral_code)) {
            return;
        }

        do {
            $code = Str::upper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        $user->forceFill(['referral_code' => $code])->save();
    }

    /**
     * Kredit poin ke referrer pada saat invitee SIGNUP.
     * Dicegah dobel via kombinasi (user_id, source_type, source_id).
     */
    public function creditForSignup(User $invitee, ?int $points = null): void
{
    if (!$invitee->referred_by) return;

    $amount = $points ?? $this->signupBonus;

    PointLedger::firstOrCreate(
        [
            'user_id'     => $invitee->referred_by,
            'source_type' => 'referral_signup',
            'source_id'   => $invitee->id,
        ],
        [
            'points'      => $amount,
            'description' => 'Referral signup bonus dari user #'.$invitee->id,
        ]
    );
}


    /**
     * Kredit poin ke referrer saat invitee AKTIF (mis. submission pertama disetujui).
     */
    public function creditForActivation(User $invitee): void
    {
        if (!$invitee->referred_by) {
            return;
        }

        PointLedger::firstOrCreate(
            [
                'user_id'     => $invitee->referred_by,
                'source_type' => 'referral_activation',
                'source_id'   => $invitee->id,
            ],
            [
                'points'      => $this->activationBonus,
                'description' => 'Referral activation bonus dari user #'.$invitee->id,
            ]
        );
    }
}
