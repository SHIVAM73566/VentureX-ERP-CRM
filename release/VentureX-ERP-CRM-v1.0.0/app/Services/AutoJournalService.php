<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\PurchaseOrder;

class AutoJournalService
{
    public static function recordInvoice(Invoice $invoice): void
    {
        $arAccount = Account::ofCompany()->where('code', '1200')->first();
        $revenueAccount = Account::ofCompany()->where('code', '4000')->first();

        if (! $arAccount || ! $revenueAccount) {
            return;
        }

        $entry = JournalEntry::create([
            'company_id' => $invoice->company_id,
            'entry_number' => NumberGenerator::next('JE', 'journal_entries'),
            'date' => $invoice->issue_date ?? now()->toDateString(),
            'description' => "Invoice {$invoice->invoice_number}",
            'reference_type' => Invoice::class,
            'reference_id' => $invoice->id,
            'status' => 'posted',
            'created_by' => $invoice->created_by,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $arAccount->id,
            'debit' => $invoice->total,
            'credit' => 0,
            'description' => "Accounts Receivable - {$invoice->customer?->name}",
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $revenueAccount->id,
            'debit' => 0,
            'credit' => $invoice->total,
            'description' => "Revenue - {$invoice->invoice_number}",
        ]);
    }

    public static function recordPayment(Payment $payment): void
    {
        $cashAccount = Account::ofCompany()->where('code', '1100')->first();
        $arAccount = Account::ofCompany()->where('code', '1200')->first();

        if (! $cashAccount || ! $arAccount) {
            return;
        }

        $entry = JournalEntry::create([
            'company_id' => $payment->company_id,
            'entry_number' => NumberGenerator::next('JE', 'journal_entries'),
            'date' => $payment->payment_date ?? now()->toDateString(),
            'description' => "Payment {$payment->payment_number}",
            'reference_type' => Payment::class,
            'reference_id' => $payment->id,
            'status' => 'posted',
            'created_by' => $payment->created_by,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $cashAccount->id,
            'debit' => $payment->amount,
            'credit' => 0,
            'description' => "Cash received - {$payment->payment_number}",
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $arAccount->id,
            'debit' => 0,
            'credit' => $payment->amount,
            'description' => 'Accounts Receivable reduction',
        ]);
    }

    public static function recordPurchaseOrder(PurchaseOrder $order): void
    {
        $inventoryAccount = Account::ofCompany()->where('code', '1300')->first();
        $apAccount = Account::ofCompany()->where('code', '2100')->first();

        if (! $inventoryAccount || ! $apAccount) {
            return;
        }

        $entry = JournalEntry::create([
            'company_id' => $order->company_id,
            'entry_number' => NumberGenerator::next('JE', 'journal_entries'),
            'date' => now()->toDateString(),
            'description' => "Purchase Order {$order->po_number}",
            'reference_type' => PurchaseOrder::class,
            'reference_id' => $order->id,
            'status' => 'posted',
            'created_by' => $order->created_by,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $inventoryAccount->id,
            'debit' => $order->total,
            'credit' => 0,
            'description' => "Inventory received - {$order->po_number}",
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $apAccount->id,
            'debit' => 0,
            'credit' => $order->total,
            'description' => "Accounts Payable - {$order->supplier?->name}",
        ]);
    }
}
