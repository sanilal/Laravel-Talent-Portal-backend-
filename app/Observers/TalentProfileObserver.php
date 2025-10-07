<?php

namespace App\Observers;

use App\Models\TalentProfile;

class TalentProfileObserver
{
    /**
     * Handle the TalentProfile "created" event.
     */
    public function created(TalentProfile $talentProfile): void
    {
        //
    }

    /**
     * Handle the TalentProfile "updated" event.
     */
    public function updated(TalentProfile $talentProfile): void
    {
        //
    }

    /**
     * Handle the TalentProfile "deleted" event.
     */
    public function deleted(TalentProfile $talentProfile): void
    {
        //
    }

    /**
     * Handle the TalentProfile "restored" event.
     */
    public function restored(TalentProfile $talentProfile): void
    {
        //
    }

    /**
     * Handle the TalentProfile "force deleted" event.
     */
    public function forceDeleted(TalentProfile $talentProfile): void
    {
        //
    }
}
