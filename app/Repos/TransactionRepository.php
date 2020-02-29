<?php

namespace App\Repos;

use App\Models\Transaction;
use App\Traits\RepositoryTrait;
use Carbon\Carbon;
use Exception;

class TransactionRepository {
	use RepositoryTrait;

	protected static $modelAttributes = ['id', 'reference_id', 'amount', 'account_number', 'debit_or_credit', 'channel',
		'currency', 'narration', 'transaction_time', 'transaction_type', 'value_date', 'balance_after', 'status'];

	public function __construct(Transaction $transaction){
		$this->transaction = $transaction;
	}


	public function all($filters = []){
		
		$start_date = isset($filters['start_date']) ? Carbon::instance(new \DateTime($filters['start_date'])) : Carbon::today()->startOfMonth();
		$end_date = isset($filters['end_date']) ? Carbon::instance(new \DateTime($filters['end_date'])) : $start_date->copy()->endOfMonth();
		$page = isset($filters['page']) ? $filters['page'] : 1;
		$limits = isset($filters['limits']) ? $filters['limits'] : 100;
		$data = $this->getFilteredAttributes($filters);
		$transactions = $this->transaction->newQuery()->orderBy('transaction_time', 'desc');

		foreach ($data as $key => $value) {

			if ( is_array($value) && count($value) > 0 ) {
				$transactions->whereIn($key, $value);
			}
			elseif($value != '' && is_string($value)){
					$transactions = $this->where($transactions, $key, $value);
				
			}

		}		

		return $transactions->whereDate('transaction_time', '>=', $start_date)->whereDate('transaction_time', '<=', $end_date)->paginate($limits);
	}

	public function filter($filters=[], $keys){
		
		$start_date = isset($filters['start_date']) ? Carbon::instance(new \DateTime($filters['start_date'])) : Carbon::today()->subMonth();
		$end_date = isset($filters['end_date']) ? Carbon::instance(new \DateTime($filters['end_date'])) : Carbon::now();
		$page = isset($filters['page']) ? $filters['page'] : 1;
		$limits = isset($filters['limits']) ? $filters['limits'] : 100;

		$data = $this->getFilteredAttributes($filters);
		$transactions = $this->transaction->newQuery()->orderBy('transaction_time', 'desc');

		foreach ($data as $key => $value) {

			if ( is_array($value) && count($value) > 0 ) {
				$transactions->whereIn($key, $value);
			}
			elseif($value != '' && is_string($value)){
					$transactions = $this->where($transactions, $key, $value);
				
			}

		}

		return $transactions->whereDate('transaction_time', '>=', $start_date)->whereDate('transaction_time', '<=', $end_date)->paginate($limits);
	}

	public function search($filters = [], $resource = false){
		$limits = isset($filters['limits']) ? $filters['limits'] : 100;
		$offset = isset($filters['offset']) ? $filters['offset'] : 0;
		$data = $this->getFilteredAttributes($filters);
		$transactions = $this->transaction->newQuery();


		foreach ($data as $key => $value) {
            
            if ( is_array($value) && count($value) > 0 ) {
				$transactions->whereIn($key, $value);
			}
			elseif($value != '' && is_string($value)){
					$transactions = $this->where($transactions, $key, $value);
				
			}

		}		

		$transactions = $transactions->offset($offset)->limit($limits)->get();

		return $transactions;
	}


	public function find(int $id){
		$transaction = $this->transaction->find($id);
       	return $transaction;
	}
	
	public function findWithAccountNo(string $account_no){
		$transaction = $this->transaction->where('account_number', $account_no)->first();
       	return $transaction;
	}
    
	public function read(int $id){
		$transaction = $this->transaction->with(['account_balance'])->find($id);
       	return $transaction;
	}

	public function export($attributes){
		return $this->search($attributes);
	}

	public function create(array $attributes){

		$data = $this->getFilteredAttributes($attributes);
       	$transaction = $this->transaction->create($data);

		return $transaction;

	}

	public function update($transaction, $attributes){

		$data = $this->getFilteredAttributes($attributes);
		$transaction->update($data);
		return $transaction;
	}


    public function delete($id){
		$transaction = $this->transaction->find($id);
		if($transaction){

			return $transaction->delete();
		}

		return false;		
	}

}