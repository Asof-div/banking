<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use App\Models\CustomerBalance;
use Carbon\Carbon;
use DateTime;

trait CustomerTrait
{

    protected function initBalance(){

        CustomerBalance::create([
            'account_number' => $this->account_number,
            'currency' => $this->currency,
        ]);
    }

    public function creditAccount($transaction){
        $account_balance = $this->account_balance;
        $value_date = Carbon::instance(new DateTime($transaction->value_date));

        if($value_date->format('Y-m-d') == (new DateTime)->format('Y-m-d')){

            $account_balance->update([
               'available_balance' => $account_balance->available_balance + $transaction->amount, 
            ]);
            $transaction->update([
                'status' => $transaction::SUCCESS_STATUS
            ]);
            $this->update([
                'last_transaction_date' => $transaction->transaction_date,
            ]);
        }else{
            $account_balance->update([
                'unclear_balance' => $account_balance->unclear_balance + $transaction->amount, 
            ]);
            $transaction->update([
                'status' => $transaction::UNCLEAR_STATUS
            ]);
            $this->update([
                'last_transaction_date' => $transaction->transaction_date,
            ]);
        }
    }

    public function debitAccount($transaction){
        $account_balance = $this->account_balance;
        $value_date = Carbon::instance(new DateTime($transaction->value_date));

        if($value_date->format('Y-m-d') == (new DateTime)->format('Y-m-d')){

            $account_balance->update([
               'available_balance' => $account_balance->available_balance - $transaction->amount - $transaction->charge_fee, 
            ]);
            $transaction->update([
                'status' => $transaction::SUCCESS_STATUS
            ]);
            $this->update([
                'last_transaction_date' => $transaction->transaction_date,
            ]);

        }else{
            $account_balance->update([
               'available_balance' => $account_balance->available_balance - $transaction->amount - $transaction->charge_fee, 
               'hold_balance' => $account_balance->hold_balance + $transaction->amount + $transaction->charge_fee, 
            ]);
            $transaction->update([
                'status' => $transaction::HOLD_STATUS
            ]);
            $this->update([
                'last_transaction_date' => $transaction->transaction_date,
            ]);
        }
    }

    public function canDebit($amount) {
        $account_balance = $this->account_balance;
        $balance = $account_balance->available_balance - $account_balance->minimum_balance;
        return $balance >= $amount;
    }


}