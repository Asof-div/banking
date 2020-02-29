<?php
namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerBalance;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DateTime;
use App\Traits\TransactionServiceTrait;

class CustomerTransactionService
{
    use TransactionServiceTrait;


    public static function atmCharge($customer, $amount){
        $start_date = Carbon::today()->startOfMonth();
        $end_date = Carbon::now()->addHour();
        $transaction_count = $customer->transactions()->whereDate('transaction_time', '>=', $start_date)->whereDate('transaction_time', '<=', $end_date)->count();

        $charge_fee = 0.00;

        if($transaction_count >= Transaction::ATM_FREE_TRANSACTION){
            $charge_fee = Transaction::ATM_TRANSACTION_CHARGE;
        }
        return $charge_fee;
    }
    
    public static function atmDebit($customer, $amount, $narration, $type, $date){
        
        $charge_fee = self::atmCharge($customer, $amount);
        $actual_amount_after_charge = $amount + Transaction::ATM_TRANSACTION_CHARGE;
        $channel = 'ATM';
    
        return self::debitAction($customer, $amount, $narration, $type, $date, $channel, $actual_amount_after_charge, $charge_fee);

    } 

    public static function posCharge($customer, $amount){
        
        $charge_fee = $amount * (Transaction::POS_TRANSACTION_PERCENT / 100);
        $charge_fee = $charge_fee > Transaction::POS_TRANSACTION_MAXIMUM ? Transaction::POS_TRANSACTION_MAXIMUM : $charge_fee;

        return $charge_fee;
    }

    public static function posDebit($customer, $amount, $narration, $type, $date){
        
        $charge_fee = self::posCharge($customer, $amount);
        $actual_amount_after_charge = $amount + $charge_fee;
        $channel = 'POS';

        return self::debitAction($customer, $amount, $narration, $type, $date, $channel, $actual_amount_after_charge, $charge_fee);

    }

    public static function eChannelCharge($customer, $amount){
        
        $charge_fee = Transaction::ETRANSFER_BELOW_5K_MAXIMUM;

        if($amount <= 5000){
            $charge_fee = $amount * (Transaction::ETRANSFER_BELOW_5K_PERCENT / 100);
            $charge_fee = $charge_fee > Transaction::ETRANSFER_BELOW_5K_MAXIMUM ? Transaction::ETRANSFER_BELOW_5K_MAXIMUM : $charge_fee;
        
        }elseif ($amount > 5000 || $amount <= 50000) {
            $charge_fee = $amount * (Transaction::ETRANSFER_BELOW_50K_PERCENT / 100);
            $charge_fee = $charge_fee > Transaction::ETRANSFER_BELOW_50K_MAXIMUM ? Transaction::ETRANSFER_BELOW_50K_MAXIMUM : $charge_fee;
        
        }else{
            $charge_fee = $amount * (Transaction::ETRANSFER_ABOVE_50K_PERCENT / 100);
            $charge_fee = $charge_fee > Transaction::ETRANSFER_ABOVE_50K_MAXIMUM ? Transaction::ETRANSFER_ABOVE_50K_MAXIMUM : $charge_fee;
        }

        return $charge_fee;
    }

    public static function eChannelTransfer($a_customer, $b_customer, $amount, $narration, $type, $date){
        
        $charge_fee = self::eChannelCharge($a_customer, $amount);

        $actual_amount_after_charge = $amount + $charge_fee;
        $ref = sprintf("%020X", rand(000000000000, 999999999999). time() );
        $refb = sprintf("%020X", rand(000000000000, 999999999999). time() );
        $channel = 'e-channel';

        $aTransaction = self::debitAction($a_customer, $amount, $narration, $type, $date, $channel, $actual_amount_after_charge, $charge_fee);
        $bTransaction = self::creditAction($b_customer, $amount, $narration, $type, $date, $channel, $aTransaction->reference_id);

        return ['a_transaction' => $aTransaction, 'b_transaction' => $bTransaction];
    }


    

}