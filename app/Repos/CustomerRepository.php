<?php

namespace App\Repos;

use App\Models\Customer;
use App\Traits\RepositoryTrait;
use Carbon\Carbon;
use Exception;

class CustomerRepository {
	use RepositoryTrait;

	protected static $modelAttributes = ['id', 'bvn', 'account_name', 'account_number', 'email', 'first_name',
		'currency', 'full_name', 'last_transaction_date', 'account_opening_date', 'state', 'last_name', 'phone', 'status'];

	public function __construct(Customer $customer){
		$this->customer = $customer;
	}


	public function all($filters = []){
		$page = isset($filters['page']) ? $filters['page'] : 1;
		$limits = isset($filters['limits']) ? $filters['limits'] : 100;
		$data = $this->getFilteredAttributes($filters);
		$customers = $this->customer->newQuery();

		foreach ($data as $key => $value) {

			if ( is_array($value) && count($value) > 0 ) {
				$customers->whereIn($key, $value);
			}
			elseif($value != '' && is_string($value)){
					$customers = $this->where($customers, $key, $value);
				
			}

		}		

		return $customers->paginate($limits);
	}


	public function search($filters = [], $resource = false){
		$limits = isset($filters['limits']) ? $filters['limits'] : 100;
		$offset = isset($filters['offset']) ? $filters['offset'] : 0;
		$data = $this->getFilteredAttributes($filters);
		$customers = $this->customer->newQuery();


		foreach ($data as $key => $value) {
            
            if ( is_array($value) && count($value) > 0 ) {
				$customers->whereIn($key, $value);
			}
			elseif($value != '' && is_string($value)){
					$customers = $this->where($customers, $key, $value);
				
			}

		}		

		$customers = $customers->offset($offset)->limit($limits)->get();

		return $customers;
	}


	public function find(int $id){
		$customer = $this->customer->find($id);
       	return $customer;
	}
	
	public function findWithAccountNo(string $account_no){
		$customer = $this->customer->where('account_number', $account_no)->first();
       	return $customer;
	}
    
	public function read(int $id){
		$customer = $this->customer->with(['account_balance'])->find($id);
       	return $customer;
	}

	public function export($attributes){
		return $this->search($attributes);
	}

	public function create(array $attributes){

		$data = $this->getFilteredAttributes($attributes);
       	$customer = $this->customer->create($data);

		return $customer;

	}

	public function update($customer, $attributes){

		$data = $this->getFilteredAttributes($attributes);
		$customer->update($data);
		return $customer;
	}


    public function delete($id){
		$customer = $this->customer->find($id);
		if($customer){

			return $customer->delete();
		}

		return false;		
	}

}