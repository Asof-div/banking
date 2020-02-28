<?php

namespace App\Traits;
use App\Services\Response\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use Validator;
use Carbon\Carbon;
use App\Services\Calendar;
use App\Models\AdminAuditLog;

trait RepositoryTrait
{
    

    protected function getFilteredAttributes($attributes, $keys=[]){
        $modelAttributes = static::getModelAttributes($keys);
        $data = [];

        foreach ($modelAttributes as $value) {
            if(array_key_exists($value, $attributes) ){
                $data[$value] = $attributes[$value];
            }
        }

        return $data;

    }


    protected static function getModelAttributes($attributes = []){

        if(count($attributes) > 0){
            return $attributes;
        }

        if(isset(static::$modelAttributes)){

            return static::$modelAttributes;
        }
        

        return [];
    }


    protected function whereClause(&$query, $key, $value){
        switch($key){
            case 'name':
                    $query->where('name', 'like', '%'.$value.'%');                  
                return $query;
                break;
            case 'phone': 
                    $query->where('phone', 'like', '%'.$value.'%');
                return $query;
                break;
            case 'email': 
                    $query->where('email', 'like', '%'.$value.'%');
                return $query;
                break;
            default:
                    $query->where($key, 'like', '%'.$value.'%');
                return $query;
                break;
        }
    }

    protected function where(&$query, $key, $value){
        $query->where($key, '=', $value);                  
        return $query;
    
    }

    protected function orWhere(&$query, $key, $value){
        $query->orWhere($key, '=', $value);                  
        return $query;
        
    }

    protected function orWhereHas(&$query, $relation, $key, $value){
        $query->orWhereHas($relation, function($q) use($value, $key){
               $q->where($key,"like",$value);
            });
        return $query;
    }

    protected function whereHas(&$query, $relation, $key, $value){
        $query->whereHas($relation, function($q) use($value, $key){
               $q->where($key,"like",$value);
            });
        return $query;
    }

    public function getCalendarDays($from, $to) {
        $calendar   = new Calendar;
        $start_date = Carbon::today()->subMonth();
        $end_date   = Carbon::now()->addHour();

        if ($from) {
            $start_date = Carbon::instance(new \DateTime($from));
        }

        if ($to) {
            $end_date = Carbon::instance(new \DateTime($to));
        }

        $days = $calendar->calculateDaysBetween($start_date, $end_date);
        return $days;
        
    }


    public function getCalendarWeeks($from, $to) {
        $calendar   = new Calendar;
        $start_date = Carbon::today()->subMonth();
        $end_date   = Carbon::now()->addHour();

        if ($from) {
            $start_date = Carbon::instance(new \DateTime($from));
        }

        if ($to) {
            $end_date = Carbon::instance(new \DateTime($to));
        }

        $days = $calendar->calculateWeeksBetween($start_date, $end_date);
        return $days;
        
    }

    public function getCalendarMonths($from, $to) {
        $calendar   = new Calendar;
        $start_date = Carbon::today()->startOfYear();
        $end_date   = Carbon::now()->endOfMonth();

        if ($from) {
            $start_date = Carbon::instance(new \DateTime($from));
        }

        if ($to) {
            $end_date = Carbon::instance(new \DateTime($to));
        }

        $days = $calendar->calculateMonthsBetween($start_date, $end_date);
        return $days;
        
    }

    static function logAction($admin_id, $action)
    {
        $newLog = new AdminAuditLog();
        $newLog->admin_id = $admin_id;
        $newLog->action = $action;
        $newLog->save();
    }
}