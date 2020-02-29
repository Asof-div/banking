<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded =  ['id'];
    
    const PENDING_STATUS = 'Pending';
    const UNCLEAR_STATUS = 'Unclear';
    const SUCCESS_STATUS = 'Success';
    const HOLD_STATUS = 'Hold';

    const CREDIT_ACTION = 'Credit';
    const DEBIT_ACTION = 'Debit';

    const ATM_TRANSACTION_CHARGE = 35;
    const ATM_FREE_TRANSACTION = 3;

    const ETRANSFER_BELOW_5K_PERCENT = 5;
    const ETRANSFER_BELOW_5K_MAXIMUM = 10;

    const ETRANSFER_BELOW_50K_PERCENT = 4.5;
    const ETRANSFER_BELOW_50K_MAXIMUM = 25;

    const ETRANSFER_ABOVE_50K_PERCENT = 5;
    const ETRANSFER_ABOVE_50K_MAXIMUM = 50;

    const POS_TRANSACTION_PERCENT = 0.75;
    const POS_TRANSACTION_MAXIMUM = 1200;



}
