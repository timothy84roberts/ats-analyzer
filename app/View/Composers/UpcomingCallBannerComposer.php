<?php

namespace App\View\Composers;

use App\Models\JobApplicationCall;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UpcomingCallBannerComposer
{
    public function compose(View $view): void
    {
        if (! Auth::check()) {
            $view->with('upcomingCallBannerCalls', collect());

            return;
        }

        $calls = JobApplicationCall::query()
            ->whereHas('application', fn ($q) => $q->where('user_id', Auth::id()))
            ->where('scheduled_at', '>', now())
            ->where('scheduled_at', '<=', now()->addHours(12))
            ->with(['application:id,title'])
            ->orderBy('scheduled_at')
            ->get();

        $view->with('upcomingCallBannerCalls', $calls);
    }
}
