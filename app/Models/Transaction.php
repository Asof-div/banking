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
    const DEBIT_ACTION = 'debit';



}
