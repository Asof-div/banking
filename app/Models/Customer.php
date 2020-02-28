<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomerTrait;

class Customer extends Model
{
    use CustomerTrait;

    const ACTIVE_STATUS = 'Active';
    const INACTIVE_STATUS = 'Inactive';
    const DORMANT_STATUS = 'Dormant';
    const FREEZING_STATUS = 'Freezing';

    const SAVING_ACCOUNT = 'Saving';
    const CURRENT_ACCOUNT = 'Current';

    protected $guarded =  ['id'];
    public static function boot(){

        parent::boot();

        static::creating(function ($model) {
            $model->account_opening_date = new \DateTime;
        });

        static::created(function ($model) {
            $model->update([
                'account_number' => sprintf("201%07X",  $model->id),
                'status' => $model::ACTIVE_STATUS               
                ]);
            $model->initBalance();
        });
    }

    public function account_balance(){

        return $this->hasOne(CustomerBalance::class, 'account_number', 'account_number');
    }

    public function transactions(){

        return $this->hasMany(Transaction::class, 'account_number', 'account_number');
    }

    public function getBalanceAttribute(){
        
        $account_balance = $this->account_balance;
        $balance = $account_balance->available_balance + $account_balance->unclear_balance;

        return $balance;
    }
    
}
