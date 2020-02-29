<?php

namespace App\Traits;

use App\Models\Transaction;
use Carbon\Carbon;
use DateTime;

trait TransactionServiceTrait
{

    public static function creditAction($customer, $amount, $narration, $type, $date, $channel, $linked=null)
    {
        
        $account_balance = $customer->account_balance;
        $balance = $account_balance->available_balance + $account_balance->unclear_balance + $amount;

        $transaction = Transaction::create([
            'account_number' => $customer->account_number,
            'amount' => $amount,
            'currency' => $customer->currency,
            'reference_id' => sprintf("%020X", rand(000000000000, 999999999999). time() ),
            'a_reference_id' => $linked,
            'narration' => $narration,
            'transaction_type' => $type,
            'value_date' => $date,
            'transaction_time' => new DateTime,
            'debit_or_credit' => Transaction::CREDIT_ACTION,
            'channel' => $channel,
            'actual_amount_after_charge' => $amount,
            'charge_fee' => 0.00,
            'balance_after' => $balance,
            'status' => Transaction::PENDING_STATUS,
        ]);

        $customer->creditAccount($transaction);

        return $transaction;
    }

    public static function debitAction($customer, $amount, $narration, $type, $date, $channel, $actual_amount, $charge_fee)
    {
        
        $account_balance = $customer->account_balance;
        $balance = ($account_balance->available_balance + $account_balance->unclear_balance) - $actual_amount;

        $transaction = Transaction::create([
            'account_number' => $customer->account_number,
            'amount' => $amount,
            'currency' => $customer->currency,
            'reference_id' => sprintf("%020X", rand(000000000000, 999999999999). time() ),
            'narration' => $narration,
            'transaction_type' => $type,
            'value_date' => $date,
            'transaction_time' => new DateTime,
            'debit_or_credit' => Transaction::DEBIT_ACTION,
            'channel' => $channel,
            'actual_amount_after_charge' => $actual_amount,
            'charge_fee' => $charge_fee,
            'balance_after' => $balance,
            'status' => Transaction::PENDING_STATUS,
        ]);

        $value_date = Carbon::instance(new DateTime($date));

        $customer->debitAccount($transaction);
        
        return $transaction;
    }

}