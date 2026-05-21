<?php

namespace App\Http\Controllers;

use App\Models\FinancialSetting;
use App\Models\FinancialTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FinancialController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Resolve selected month first
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        try {
            $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        } catch (\Exception $e) {
            $monthDate = Carbon::now()->startOfMonth();
            $selectedMonth = $monthDate->format('Y-m');
        }

        // Load settings for the selected month; inherit most-recent prior values if none exist yet
        $settings = FinancialSetting::where('user_id', $user->id)
            ->where('year_month', $selectedMonth)
            ->first();

        if (! $settings) {
            $previous = FinancialSetting::where('user_id', $user->id)
                ->where('year_month', '<', $selectedMonth)
                ->orderBy('year_month', 'desc')
                ->first();

            $settings = FinancialSetting::create([
                'user_id'              => $user->id,
                'year_month'           => $selectedMonth,
                'default_remaining'    => $previous ? $previous->default_remaining    : 1250,
                'additional_remaining' => $previous ? $previous->additional_remaining : 1700,
            ]);
        }

        $monthStart = $monthDate->copy()->startOfMonth()->toDateString();
        $monthEnd   = $monthDate->copy()->endOfMonth()->toDateString();

        $monthTransactions = FinancialTransaction::where('user_id', $user->id)
            ->whereBetween('transacted_at', [$monthStart, $monthEnd])
            ->orderBy('transacted_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $monthIncome  = (float) $monthTransactions->where('type', 'income')->sum('amount');
        $monthExpense = (float) $monthTransactions->where('type', 'expense')->sum('amount');
        $monthNet     = $monthIncome - $monthExpense;

        $defaultMet    = $monthNet >= (float) $settings->default_remaining;
        $additionalMet = ($monthNet - (float) $settings->default_remaining) >= (float) $settings->additional_remaining;

        // Month selector options (last 24 months, newest first)
        $monthOptions = [];
        for ($i = 0; $i < 24; $i++) {
            $m = Carbon::now()->subMonths($i);
            $monthOptions[$m->format('Y-m')] = $m->format('F Y');
        }

        // 12-month chart data (always relative to today)
        $now = Carbon::now();
        $chartLabels  = [];
        $chartIncome  = [];
        $chartExpense = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $start = $month->copy()->startOfMonth()->toDateString();
            $end   = $month->copy()->endOfMonth()->toDateString();

            $rows = FinancialTransaction::where('user_id', $user->id)
                ->whereBetween('transacted_at', [$start, $end])
                ->selectRaw('type, SUM(amount) as total')
                ->groupBy('type')
                ->pluck('total', 'type');

            $chartLabels[]  = $month->format('M Y');
            $chartIncome[]  = (float) ($rows['income'] ?? 0);
            $chartExpense[] = (float) ($rows['expense'] ?? 0);
        }

        return view('financial.index', compact(
            'settings',
            'monthTransactions',
            'monthIncome',
            'monthExpense',
            'monthNet',
            'defaultMet',
            'additionalMet',
            'chartLabels',
            'chartIncome',
            'chartExpense',
            'selectedMonth',
            'monthOptions',
            'monthDate',
        ));
    }

    public function downloadPdf(Request $request): Response
    {
        $user = auth()->user();

        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        try {
            $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        } catch (\Exception $e) {
            $monthDate = Carbon::now()->startOfMonth();
            $selectedMonth = $monthDate->format('Y-m');
        }

        $monthStart = $monthDate->copy()->startOfMonth()->toDateString();
        $monthEnd   = $monthDate->copy()->endOfMonth()->toDateString();

        $transactions = FinancialTransaction::where('user_id', $user->id)
            ->whereBetween('transacted_at', [$monthStart, $monthEnd])
            ->orderBy('transacted_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $monthIncome  = (float) $transactions->where('type', 'income')->sum('amount');
        $monthExpense = (float) $transactions->where('type', 'expense')->sum('amount');
        $monthNet     = $monthIncome - $monthExpense;

        $settings = FinancialSetting::where('user_id', $user->id)
            ->where('year_month', $selectedMonth)
            ->first() ?? new FinancialSetting(['default_remaining' => 1250, 'additional_remaining' => 1700]);

        $defaultMet    = $monthNet >= (float) $settings->default_remaining;
        $additionalMet = ($monthNet - (float) $settings->default_remaining) >= (float) $settings->additional_remaining;

        $pdf = Pdf::loadView('financial.pdf', compact(
            'user', 'monthDate', 'transactions',
            'monthIncome', 'monthExpense', 'monthNet',
            'settings', 'defaultMet', 'additionalMet',
        ))->setPaper('a4', 'portrait');

        $filename = 'financial-' . $selectedMonth . '.pdf';

        return $pdf->download($filename);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'type'           => 'required|in:income,expense',
            'note'           => 'nullable|string|max:1000',
            'transacted_at'  => 'required|date',
        ]);

        FinancialTransaction::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        return back()->with('status', __('Transaction added.'));
    }

    public function update(Request $request, FinancialTransaction $transaction): RedirectResponse
    {
        abort_if($transaction->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0.01',
            'type'          => 'required|in:income,expense',
            'note'          => 'nullable|string|max:1000',
            'transacted_at' => 'required|date',
        ]);

        $transaction->update($validated);

        return back()->with('status', __('Transaction updated.'));
    }

    public function destroy(FinancialTransaction $transaction): RedirectResponse
    {
        abort_if($transaction->user_id !== auth()->id(), 403);

        $transaction->delete();

        return back()->with('status', __('Transaction deleted.'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year_month'           => 'required|string|size:7',
            'default_remaining'    => 'required|numeric|min:0',
            'additional_remaining' => 'required|numeric|min:0',
        ]);

        FinancialSetting::updateOrCreate(
            ['user_id' => auth()->id(), 'year_month' => $validated['year_month']],
            [
                'default_remaining'    => $validated['default_remaining'],
                'additional_remaining' => $validated['additional_remaining'],
            ]
        );

        return back()->with('status', __('Targets saved for :month.', ['month' => Carbon::createFromFormat('Y-m', $validated['year_month'])->format('F Y')]));
    }
}
