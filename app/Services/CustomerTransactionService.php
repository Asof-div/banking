<?php
namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerBalance;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DateTime;

class CustomerTransactionService
{

    public static function credit($customer, $amount, $narration, $type, $date, $channel)
    {
        
        $account_balance = $customer->account_balance;
        $balance = $account_balance->available_balance + $account_balance->unclear_balance + $amount;

        $transaction = Transaction::create([
            'account_number' => $customer->account_number,
            'amount' => $amount,
            'currency' => $customer->currency,
            'reference_id' => sprintf("%020X", rand(000000000000, 999999999999). time() ),
            'narration' => $narration,
            'transaction_type' => $type,
            'value_date' => $date,
            'transaction_time' => new DateTime,
            'debit_or_credit' => Transaction::CREDIT_ACTION,
            'channel' => $channel,
            'balance_after' => $balance,
            'status' => Transaction::PENDING_STATUS,
        ]);

        $customer->creditAccount($transaction);

        return $transaction;
    }

    public static function debit($customer, $amount, $narration, $type, $date, $channel)
    {
        
        $account_balance = $customer->account_balance;
        $balance = ($account_balance->available_balance + $account_balance->unclear_balance) - $amount;

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
            'balance_after' => $balance,
            'status' => Transaction::PENDING_STATUS,
        ]);

        $value_date = Carbon::instance(new DateTime($date));

        $customer->debitAccount($transaction);
        
        return $transaction;
    }

    
}