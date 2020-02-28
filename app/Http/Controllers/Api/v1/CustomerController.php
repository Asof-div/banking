<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Repos\CustomerRepository;
use App\Traits\APIResponseTrait;

class CustomerController extends Controller
{
	use APIResponseTrait;

    protected static $modelName = 'Customer';

    public function __construct(CustomerRepository $customerRepo){
        $this->customerRepo = $customerRepo;
    }
    
    public function index(Request $request){

        $customers = $this->customerRepo->all($request->all());

        return $this->success([
            'customers' => $customers,
        ]);
    }

    public function show(Request $request, $account_no)
    {
        
        $customer = $this->customerRepo->findWithAccountNo($account_no);

        if (!$customer) {
            return $this->notFoundResponse();
        }
        $customer->load(['account_balance']);

        return $this->success(['customer' => $customer]);

    }

    public function create(Request $request)
    {
        $validator = $this->handleValidation([
            'last_name' => 'required',
            'first_name' => 'required',
            'account_name' => 'required',
            'account_type' => 'required',
            'currency' => 'required|min:3|max:3',
        ]);
    
        if ($validator){ 
        
            return $validator;
        }

        $bvns = $this->customerRepo->all([])->pluck('bvn');
        $bvn = $this->generate($bvns);
        $params = $request->all();
        $params['bvn'] = $bvn;
        $params['full_name'] = $request->first_name .' '. $request->last_name;    
        $params['status'] = '';    
        
        $customer = $this->customerRepo->create($params);

        return $this->success(['customer' => $customer, 'msg' => 'Created a new customer']);
    }

    public function update(Request $request, $account_no)
    {

        $validator = $this->handleValidation([
            
            'full_name' => 'required',
            'last_name' => 'required',
            'first_name' => 'required',
            'currency' => 'required|min:3|max:3',
        ]);
        
        if ($validator){ 
        
            return $validator;
        }

        $customer = $this->customerRepo->findWithAccountNo($account_no);

        if (!$customer) {
            return $this->notFoundResponse();
        }

        $params = $request->all();
        unset($params['status']);
        $customer = $this->customerRepo->update($customer, $params);

        return $this->success(['customer' => $customer, 'msg' => 'Updated a customer']);
    }

    public function freezeAccount(Request $request, $account_no)
    {

        $customer = $this->customerRepo->findWithAccountNo($account_no);

        if (!$customer) {
            return $this->notFoundResponse();
        }
        $params = ['status' => $customer::FREEZING_STATUS];
        $customer = $this->customerRepo->update($customer, $params);

        return $this->success(['customer' => $customer, 'msg' => 'Customer account has been frozen']);
    }

    public function unfreezeAccount(Request $request, $account_no)
    {

        $customer = $this->customerRepo->findWithAccountNo($account_no);

        if (!$customer) {
            return $this->notFoundResponse();
        }
        $params = ['status' => $customer::ACTIVE_STATUS];
        $customer = $this->customerRepo->update($customer, $params);

        return $this->success(['customer' => $customer, 'msg' => 'Customer account has been unfrozen']);
    }
    
    public function checkBalance(Request $request, $account_no)
    {

        $customer = $this->customerRepo->findWithAccountNo($account_no);

        if (!$customer) {
            return $this->notFoundResponse();
        }

        return $this->success([
                'customer' => $customer, 
                'balance' => $customer->balance, 
                'account_balance' => $customer->account_balance]);
    }
    
    public function delete(Request $request, $account_no)
    {
        $customer = $this->customerRepo->findWithAccountNo($account_no);
        if (!$customer) {
            return $this->notFoundResponse();
        }
    
        $customer->delete();

        return $this->success(['msg' => 'Deleted a customer']);
    }

    function generate($bvns, $size=11){

        $code = substr( sprintf("%02d", rand(0000, 9999). time() ) , 0, $size);
        
        if($bvns->search($code)){
            return $this->generate($bvns);
        }
        return $code;
    }

}
