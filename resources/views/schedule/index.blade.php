<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;border-radius:10px;background:var(--admin-accent-muted);color:var(--admin-accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
            </div>
            <div>
                <h1 style="margin:0;">{{ __('Schedule') }}</h1>
                <div style="font-size:0.8rem;color:var(--admin-text-muted);margin-top:1px;">{{ __('Your call reservations at a glance') }}</div>
            </div>
        </div>
    </x-slot>

    <div class="admin-card admin-card--pad" x-data="scheduleApp(@js($calls))">

        {{-- Toolbar --}}
        <div class="cal-toolbar">
            <div class="cal-toolbar__nav">
                <button type="button" class="admin-btn admin-btn--ghost cal-today" @click="goToday()">{{ __('Today') }}</button>
                <div class="cal-arrows">
                    <button type="button" class="cal-arrow" @click="prev()" aria-label="{{ __('Previous') }}">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    </button>
                    <button type="button" class="cal-arrow" @click="next()" aria-label="{{ __('Next') }}">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </button>
                </div>
                <div class="cal-title" x-text="title"></div>
            </div>

            <div class="cal-viewswitch">
                <button type="button" :class="view==='day' ? 'is-active' : ''" @click="setView('day')">{{ __('Day') }}</button>
                <button type="button" :class="view==='week' ? 'is-active' : ''" @click="setView('week')">{{ __('Week') }}</button>
                <button type="button" :class="view==='month' ? 'is-active' : ''" @click="setView('month')">{{ __('Month') }}</button>
            </div>
        </div>

        {{-- ───────────────── MONTH VIEW ───────────────── --}}
        <div x-show="view==='month'" x-cloak class="cal-month">
            <div class="cal-weekdays">
                <template x-for="(wd, i) in weekdayNames" :key="i">
                    <div class="cal-weekday" x-text="wd"></div>
                </template>
            </div>
            <div class="cal-month-grid">
                <template x-for="(day, i) in monthDays" :key="i">
                    <div class="cal-cell" :class="{ 'is-outside': !day.inMonth, 'is-today': day.isToday }">
                        <button type="button" class="cal-daynum" @click="goToDay(day.date)" x-text="day.date.getDate()"></button>
                        <div class="cal-cell-events">
                            <template x-for="ev in day.events.slice(0, 3)" :key="ev.id">
                                <button type="button" class="cal-chip" :class="{ 'is-past': isPast(ev.startDate), 'is-soon': isSoon(ev.startDate) }" @click="openEvent(ev)">
                                    <span class="cal-chip__dot"></span>
                                    <span class="cal-chip__time" x-text="fmtTime(ev.startDate)"></span>
                                    <span class="cal-chip__title" x-text="ev.title"></span>
                                </button>
                            </template>
                            <button type="button" class="cal-more" x-show="day.events.length > 3" @click="goToDay(day.date)"
                                    x-text="'+' + (day.events.length - 3) + ' {{ __('more') }}'"></button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ───────────────── WEEK VIEW ───────────────── --}}
        <div x-show="view==='week'" x-cloak class="cal-week">
            <template x-for="(day, i) in weekDays" :key="i">
                <div class="cal-week-col" :class="{ 'is-today': day.isToday }">
                    <button type="button" class="cal-week-head" @click="goToDay(day.date)">
                        <span class="cal-week-head__wd" x-text="weekdayNames[day.date.getDay()]"></span>
                        <span class="cal-week-head__num" x-text="day.date.getDate()"></span>
                    </button>
                    <div class="cal-week-body">
                        <template x-for="ev in day.events" :key="ev.id">
                            <button type="button" class="cal-event-card" :class="{ 'is-past': isPast(ev.startDate), 'is-soon': isSoon(ev.startDate) }" @click="openEvent(ev)">
                                <span class="cal-event-card__time">
                                    <span x-text="fmtTime(ev.startDate)"></span>
                                    <span class="cal-badge cal-badge--danger" x-show="isPast(ev.startDate)">{{ __('Expired') }}</span>
                                    <span class="cal-badge cal-badge--warning cal-badge--lg" x-show="isSoon(ev.startDate)">{{ __('Soon') }}</span>
                                </span>
                                <span class="cal-event-card__title" x-text="ev.title"></span>
                                <span class="cal-event-card__sub" x-show="ev.application_title" x-text="ev.company_name || ev.application_title"></span>
                            </button>
                        </template>
                        <div class="cal-empty-mini" x-show="!day.events.length">{{ __('No calls') }}</div>
                    </div>
                </div>
            </template>
        </div>

        {{-- ───────────────── DAY VIEW ───────────────── --}}
        <div x-show="view==='day'" x-cloak class="cal-day-wrap">
            <div class="cal-day-scroll" x-ref="dayScroll">
                <div class="cal-day-inner" :style="`height:${24 * hourHeight}px`">
                    <template x-for="h in 24" :key="h">
                        <div class="cal-hour" :style="`top:${(h - 1) * hourHeight}px;height:${hourHeight}px`">
                            <span class="cal-hour-label" x-text="fmtHour(h - 1)"></span>
                        </div>
                    </template>

                    <div class="cal-now" x-show="nowLine !== null" :style="nowLine"></div>

                    <div class="cal-day-col">
                        <template x-for="p in dayLayout" :key="p.ev.id">
                            <button type="button" class="cal-day-event" :class="{ 'is-past': isPast(p.ev.startDate), 'is-soon': isSoon(p.ev.startDate) }" :style="p.style" @click="openEvent(p.ev)">
                                <span class="cal-day-event__time" x-text="fmtTime(p.ev.startDate)"></span>
                                <span class="cal-day-event__title" x-text="p.ev.title"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            <div class="cal-empty" x-show="!dayLayout.length">
                <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                <p style="margin:8px 0 0;">{{ __('No calls scheduled for this day.') }}</p>
            </div>
        </div>

        {{-- ───────────────── EVENT DETAIL MODAL ───────────────── --}}
        <div x-show="selected" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             style="position:fixed;inset:0;z-index:300;">
            <div style="position:absolute;inset:0;background:rgba(0,0,0,0.45);" @click="selected = null"></div>
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:16px;pointer-events:none;">
                <div class="admin-modal-panel" style="position:relative;pointer-events:auto;width:100%;max-width:440px;border-radius:var(--admin-radius);padding:24px 26px 26px;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px;">
                        <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                            <span style="width:38px;height:38px;border-radius:10px;background:var(--admin-accent-muted);color:var(--admin-accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                            </span>
                            <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:var(--admin-text);overflow-wrap:anywhere;" x-text="selected?.title"></h2>
                        </div>
                        <button type="button" @click="selected = null" style="background:none;border:none;cursor:pointer;color:var(--admin-text-muted);padding:4px;line-height:1;flex-shrink:0;">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="cal-detail-row">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <span x-text="selected ? fmtFull(selected.startDate) : ''"></span>
                        <span class="cal-badge cal-badge--danger" x-show="selected && isPast(selected.startDate)">{{ __('Expired') }}</span>
                        <span class="cal-badge cal-badge--warning cal-badge--lg" x-show="selected && isSoon(selected.startDate)">{{ __('Soon') }}</span>
                    </div>
                    <template x-if="selected && (selected.application_title || selected.company_name)">
                        <div class="cal-detail-row">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                            <span x-text="selected ? (selected.application_title || '') + (selected.company_name ? ' · ' + selected.company_name : '') : ''"></span>
                        </div>
                    </template>
                    <template x-if="selected && selected.description">
                        <div class="cal-detail-desc" x-text="selected?.description"></div>
                    </template>

                    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:22px;">
                        <button type="button" class="admin-btn admin-btn--ghost" @click="selected = null">{{ __('Close') }}</button>
                        <template x-if="selected && selected.url">
                            <a :href="selected.url" class="admin-btn admin-btn--primary" style="text-decoration:none;">{{ __('Open application') }}</a>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            [x-cloak] { display: none !important; }

            .cal-toolbar { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:18px; }
            .cal-toolbar__nav { display:flex; align-items:center; gap:12px; }
            .cal-today { height:36px; padding:0 14px; font-size:0.82rem; }
            .cal-arrows { display:flex; align-items:center; gap:4px; }
            .cal-arrow { width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--admin-border); background:var(--admin-surface); color:var(--admin-text); border-radius:var(--admin-radius-sm); cursor:pointer; transition:background .15s; }
            .cal-arrow:hover { background:var(--admin-surface-2); }
            .cal-title { font-size:1.05rem; font-weight:700; color:var(--admin-text); white-space:nowrap; }

            .cal-viewswitch { display:inline-flex; border:1px solid var(--admin-border); border-radius:var(--admin-radius-sm); overflow:hidden; }
            .cal-viewswitch button { padding:8px 16px; font-size:0.82rem; font-weight:600; background:var(--admin-surface); color:var(--admin-text-muted); border:none; cursor:pointer; border-left:1px solid var(--admin-border); transition:background .15s,color .15s; }
            .cal-viewswitch button:first-child { border-left:none; }
            .cal-viewswitch button.is-active { background:var(--admin-accent); color:#fff; }

            /* Month */
            .cal-weekdays { display:grid; grid-template-columns:repeat(7,1fr); gap:0; }
            .cal-weekday { text-align:center; font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; color:var(--admin-text-muted); padding:6px 0; }
            .cal-month-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:1px; background:var(--admin-border); border:1px solid var(--admin-border); border-radius:var(--admin-radius-sm); overflow:hidden; }
            .cal-cell { min-width:0; min-height:110px; padding:6px; display:flex; flex-direction:column; gap:4px; background:var(--admin-surface); }
            .cal-cell.is-outside { background:var(--admin-surface-2); }
            .cal-cell.is-outside .cal-daynum { color:var(--admin-text-muted); opacity:0.6; }
            .cal-daynum { align-self:flex-start; width:26px; height:26px; border-radius:50%; border:none; background:none; cursor:pointer; font-size:0.8rem; font-weight:600; color:var(--admin-text); display:inline-flex; align-items:center; justify-content:center; }
            .cal-daynum:hover { background:var(--admin-surface-2); }
            .cal-cell.is-today .cal-daynum { background:var(--admin-accent); color:#fff; }
            .cal-cell-events { display:flex; flex-direction:column; gap:3px; overflow:hidden; min-width:0; }
            .cal-chip { display:flex; align-items:center; gap:5px; width:100%; max-width:100%; text-align:left; border:none; background:var(--admin-accent-muted); border-radius:6px; padding:3px 6px; cursor:pointer; font-size:0.72rem; color:var(--admin-text); overflow:hidden; min-width:0; }
            .cal-chip:hover { filter:brightness(0.97); }
            .cal-chip__dot { width:6px; height:6px; border-radius:50%; background:var(--admin-accent); flex-shrink:0; }
            .cal-chip__time { font-weight:700; color:var(--admin-accent-text); flex-shrink:0; }
            .cal-chip__title { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
            .cal-more { border:none; background:none; cursor:pointer; font-size:0.7rem; font-weight:600; color:var(--admin-text-muted); text-align:left; padding:1px 6px; }
            .cal-more:hover { color:var(--admin-accent); }

            /* Week */
            .cal-week { display:grid; grid-template-columns:repeat(7,1fr); border:1px solid var(--admin-border); border-radius:var(--admin-radius-sm); overflow:hidden; }
            .cal-week-col { border-right:1px solid var(--admin-border); display:flex; flex-direction:column; min-width:0; }
            .cal-week-col:last-child { border-right:none; }
            .cal-week-col.is-today { background:var(--admin-accent-muted); }
            .cal-week-head { display:flex; flex-direction:column; align-items:center; gap:2px; padding:8px 4px; border:none; border-bottom:1px solid var(--admin-border); background:transparent; cursor:pointer; }
            .cal-week-head__wd { font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; color:var(--admin-text-muted); }
            .cal-week-head__num { font-size:1.05rem; font-weight:700; color:var(--admin-text); }
            .cal-week-col.is-today .cal-week-head__num { color:var(--admin-accent-text); }
            .cal-week-body { display:flex; flex-direction:column; gap:6px; padding:8px 6px; min-height:240px; }
            .cal-event-card { display:flex; flex-direction:column; gap:1px; text-align:left; border:none; border-left:3px solid var(--admin-accent); background:var(--admin-accent-muted); border-radius:6px; padding:6px 8px; cursor:pointer; }
            .cal-event-card:hover { filter:brightness(0.97); }
            .cal-event-card__time { display:flex; align-items:center; flex-wrap:wrap; gap:4px; font-size:0.72rem; font-weight:700; color:var(--admin-accent-text); }
            .cal-event-card__title { font-size:0.8rem; font-weight:600; color:var(--admin-text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
            .cal-event-card__sub { font-size:0.7rem; color:var(--admin-text-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
            .cal-empty-mini { font-size:0.72rem; color:var(--admin-text-muted); text-align:center; padding-top:12px; opacity:0.7; }

            /* Day */
            .cal-day-wrap { position:relative; border:1px solid var(--admin-border); border-radius:var(--admin-radius-sm); overflow:hidden; }
            .cal-day-scroll { max-height:600px; overflow-y:auto; }
            .cal-day-inner { position:relative; }
            .cal-hour { position:absolute; left:0; right:0; border-top:1px solid var(--admin-border); }
            .cal-hour-label { position:absolute; left:6px; top:-8px; font-size:0.68rem; color:var(--admin-text-muted); background:var(--admin-surface); padding:0 4px; }
            .cal-day-col { position:absolute; left:58px; right:10px; top:0; bottom:0; }
            .cal-day-event { position:absolute; box-sizing:border-box; display:flex; flex-direction:column; gap:1px; text-align:left; overflow:hidden; border:none; border-left:3px solid var(--admin-accent); background:var(--admin-accent-muted); border-radius:6px; padding:3px 7px; cursor:pointer; }
            .cal-day-event:hover { filter:brightness(0.97); }
            .cal-day-event__time { font-size:0.68rem; font-weight:700; color:var(--admin-accent-text); }
            .cal-day-event__title { font-size:0.78rem; font-weight:600; color:var(--admin-text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
            .cal-now { position:absolute; left:58px; right:10px; height:0; border-top:2px solid #ef4444; z-index:5; }
            .cal-now::before { content:''; position:absolute; left:-5px; top:-5px; width:8px; height:8px; border-radius:50%; background:#ef4444; }

            .cal-empty { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; color:var(--admin-text-muted); pointer-events:none; }

            .cal-detail-row { display:flex; align-items:center; gap:10px; font-size:0.875rem; color:var(--admin-text); margin-bottom:10px; }
            .cal-detail-row svg { color:var(--admin-text-muted); flex-shrink:0; }
            .cal-detail-desc { white-space:pre-line; font-size:0.85rem; color:var(--admin-text); background:var(--admin-surface-2); border:1px solid var(--admin-border); border-radius:8px; padding:10px 12px; margin-top:4px; max-height:40vh; overflow-y:auto; }

            /* Call status badges */
            .cal-badge { display:inline-block; padding:1px 6px; border-radius:999px; font-size:0.62rem; font-weight:700; text-transform:uppercase; letter-spacing:0.03em; vertical-align:middle; }
            .cal-badge--danger { background:rgba(239,68,68,0.14); color:#dc2626; }
            .cal-badge--warning { background:rgba(245,158,11,0.18); color:#d97706; }
            .cal-badge--lg { padding:3px 10px; font-size:0.72rem; letter-spacing:0.04em; }
            html[data-theme="dark"] .cal-badge--danger { color:#f87171; }
            html[data-theme="dark"] .cal-badge--warning { color:#fbbf24; }

            /* Expired (past) calls */
            .cal-chip.is-past { background:rgba(239,68,68,0.12); }
            .cal-chip.is-past .cal-chip__dot { background:#dc2626; }
            .cal-chip.is-past .cal-chip__time { color:#dc2626; }
            .cal-chip.is-past .cal-chip__title { color:var(--admin-text-muted); text-decoration:line-through; }

            /* Soon (within 12 hours) calls */
            .cal-chip.is-soon { background:rgba(245,158,11,0.14); padding:5px 8px; font-size:0.78rem; }
            .cal-chip.is-soon .cal-chip__dot { width:8px; height:8px; background:#d97706; }
            .cal-chip.is-soon .cal-chip__time { color:#d97706; font-size:0.76rem; }
            .cal-chip.is-soon .cal-chip__title { font-weight:700; }

            .cal-event-card.is-past { border-left-color:#dc2626; background:rgba(239,68,68,0.1); }
            .cal-event-card.is-past .cal-event-card__time { color:#dc2626; }
            .cal-event-card.is-past .cal-event-card__title { color:var(--admin-text-muted); text-decoration:line-through; }

            .cal-event-card.is-soon { border-left-color:#d97706; background:rgba(245,158,11,0.12); padding:9px 10px; }
            .cal-event-card.is-soon .cal-event-card__time { color:#d97706; font-size:0.78rem; }
            .cal-event-card.is-soon .cal-event-card__title { font-size:0.86rem; font-weight:700; }

            .cal-day-event.is-past { border-left-color:#dc2626; background:rgba(239,68,68,0.1); }
            .cal-day-event.is-past .cal-day-event__time { color:#dc2626; }
            .cal-day-event.is-past .cal-day-event__title { color:var(--admin-text-muted); text-decoration:line-through; }

            .cal-day-event.is-soon { border-left-color:#d97706; background:rgba(245,158,11,0.12); padding:5px 9px; }
            .cal-day-event.is-soon .cal-day-event__time { color:#d97706; font-size:0.74rem; }
            .cal-day-event.is-soon .cal-day-event__title { font-size:0.84rem; font-weight:700; }

            @media (max-width:640px) {
                .cal-cell { min-height:78px; }
                .cal-chip__title { display:none; }
                .cal-week-body { min-height:140px; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function scheduleApp(rawCalls) {
                return {
                    view: 'month',
                    cursor: new Date(),
                    selected: null,
                    hourHeight: 56,
                    weekdayNames: ['{{ __('Sun') }}', '{{ __('Mon') }}', '{{ __('Tue') }}', '{{ __('Wed') }}', '{{ __('Thu') }}', '{{ __('Fri') }}', '{{ __('Sat') }}'],
                    events: [],

                    init() {
                        this.events = (rawCalls || [])
                            .filter(c => c.start)
                            .map(c => ({ ...c, startDate: new Date(c.start) }));
                        this.cursor = this.startOfDay(new Date());
                        this.$watch('view', () => { if (this.view === 'day') this.scrollDay(); });
                    },

                    /* ---------- helpers ---------- */
                    startOfDay(d) { const x = new Date(d); x.setHours(0, 0, 0, 0); return x; },
                    addDays(d, n) { const x = new Date(d); x.setDate(x.getDate() + n); return x; },
                    addMonths(d, n) { const x = new Date(d); x.setMonth(x.getMonth() + n); return x; },
                    startOfWeek(d) { const x = this.startOfDay(d); x.setDate(x.getDate() - x.getDay()); return x; },
                    sameDay(a, b) { return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate(); },
                    isToday(d) { return this.sameDay(d, new Date()); },
                    isPast(d) { return d.getTime() < Date.now(); },
                    isSoon(d) {
                        const now = Date.now();
                        const t = d.getTime();
                        return t >= now && t <= now + 12 * 3600000;
                    },

                    fmtTime(d) { return d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }); },
                    fmtHour(h) {
                        const ampm = h < 12 ? 'AM' : 'PM';
                        const hr = h % 12 === 0 ? 12 : h % 12;
                        return hr + ' ' + ampm;
                    },
                    fmtFull(d) { return d.toLocaleDateString([], { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + ' · ' + this.fmtTime(d); },

                    eventsForDay(date) {
                        return this.events
                            .filter(e => this.sameDay(e.startDate, date))
                            .sort((a, b) => a.startDate - b.startDate);
                    },

                    /* ---------- navigation ---------- */
                    setView(v) { this.view = v; },
                    goToday() { this.cursor = this.startOfDay(new Date()); },
                    goToDay(date) { this.cursor = this.startOfDay(date); this.view = 'day'; },
                    prev() {
                        if (this.view === 'month') this.cursor = this.addMonths(this.cursor, -1);
                        else if (this.view === 'week') this.cursor = this.addDays(this.cursor, -7);
                        else this.cursor = this.addDays(this.cursor, -1);
                    },
                    next() {
                        if (this.view === 'month') this.cursor = this.addMonths(this.cursor, 1);
                        else if (this.view === 'week') this.cursor = this.addDays(this.cursor, 7);
                        else this.cursor = this.addDays(this.cursor, 1);
                    },

                    openEvent(ev) { this.selected = ev; },

                    scrollDay() {
                        this.$nextTick(() => {
                            const el = this.$refs.dayScroll;
                            if (!el) return;
                            const layout = this.dayLayout;
                            let targetHour = 7;
                            if (layout.length) {
                                targetHour = Math.max(0, layout[0].ev.startDate.getHours() - 1);
                            }
                            el.scrollTop = targetHour * this.hourHeight;
                        });
                    },

                    /* ---------- title ---------- */
                    get title() {
                        if (this.view === 'month') {
                            return this.cursor.toLocaleDateString([], { month: 'long', year: 'numeric' });
                        }
                        if (this.view === 'week') {
                            const start = this.startOfWeek(this.cursor);
                            const end = this.addDays(start, 6);
                            const sameMonth = start.getMonth() === end.getMonth();
                            const startStr = start.toLocaleDateString([], { month: 'short', day: 'numeric' });
                            const endStr = end.toLocaleDateString([], sameMonth ? { day: 'numeric' } : { month: 'short', day: 'numeric' });
                            return `${startStr} – ${endStr}, ${end.getFullYear()}`;
                        }
                        return this.cursor.toLocaleDateString([], { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
                    },

                    /* ---------- month grid ---------- */
                    get monthDays() {
                        const first = new Date(this.cursor.getFullYear(), this.cursor.getMonth(), 1);
                        const gridStart = this.startOfWeek(first);
                        const last = new Date(this.cursor.getFullYear(), this.cursor.getMonth() + 1, 0);
                        const gridEnd = this.addDays(this.startOfWeek(last), 6);
                        const totalDays = Math.round((gridEnd - gridStart) / 86400000) + 1;
                        const cells = [];
                        for (let i = 0; i < totalDays; i++) {
                            const date = this.addDays(gridStart, i);
                            cells.push({
                                date,
                                inMonth: date.getMonth() === this.cursor.getMonth(),
                                isToday: this.isToday(date),
                                events: this.eventsForDay(date),
                            });
                        }
                        return cells;
                    },

                    /* ---------- week ---------- */
                    get weekDays() {
                        const start = this.startOfWeek(this.cursor);
                        const days = [];
                        for (let i = 0; i < 7; i++) {
                            const date = this.addDays(start, i);
                            days.push({ date, isToday: this.isToday(date), events: this.eventsForDay(date) });
                        }
                        return days;
                    },

                    /* ---------- day layout (positioned, overlap-aware) ---------- */
                    get dayLayout() {
                        const evs = this.eventsForDay(this.cursor);
                        const out = [];
                        const DURATION = 60 * 60000;
                        const endOf = e => e.startDate.getTime() + DURATION;
                        let i = 0;
                        while (i < evs.length) {
                            const group = [evs[i]];
                            let groupEnd = endOf(evs[i]);
                            let j = i + 1;
                            while (j < evs.length && evs[j].startDate.getTime() < groupEnd) {
                                group.push(evs[j]);
                                groupEnd = Math.max(groupEnd, endOf(evs[j]));
                                j++;
                            }
                            const laneEnds = [];
                            group.forEach(ev => {
                                let lane = laneEnds.findIndex(end => end <= ev.startDate.getTime());
                                if (lane === -1) { lane = laneEnds.length; laneEnds.push(0); }
                                laneEnds[lane] = endOf(ev);
                                ev._lane = lane;
                            });
                            const cols = laneEnds.length;
                            group.forEach(ev => {
                                const minutes = ev.startDate.getHours() * 60 + ev.startDate.getMinutes();
                                const top = (minutes / 60) * this.hourHeight;
                                const height = this.isSoon(ev.startDate)
                                    ? Math.max(this.hourHeight + 4, 40)
                                    : Math.max(this.hourHeight - 3, 22);
                                const widthPct = 100 / cols;
                                const leftPct = ev._lane * widthPct;
                                out.push({
                                    ev,
                                    style: `top:${top}px;height:${height}px;left:calc(${leftPct}% + 3px);width:calc(${widthPct}% - 6px);`,
                                });
                            });
                            i = j;
                        }
                        return out;
                    },

                    get nowLine() {
                        if (!this.isToday(this.cursor)) return null;
                        const now = new Date();
                        const minutes = now.getHours() * 60 + now.getMinutes();
                        const top = (minutes / 60) * this.hourHeight;
                        return `top:${top}px`;
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
