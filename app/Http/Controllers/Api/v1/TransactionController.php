<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\APIResponseTrait;
use App\Repos\CustomerRepository;
use App\Services\CustomerTransactionService;
use DB;

class TransactionController extends Controller
{
    use APIResponseTrait;

    protected static $modelName = 'Transaction';

    public function __construct(CustomerRepository $customerRepo){
        $this->customerRepo = $customerRepo;
    }

    public function index(Request $request, $account_no){

        $customer = $this->customerRepo->findWithAccountNo($account_no);

        if (!$customer) {
            return $this->notFoundResponse('Account details not found');
        }

        $transactions = Transaction::where('account_number', $account_no)
            ->orderBy('transaction_date', 'desc')->paginate(20);

        return $this->success([
            'transactions' => $transactions,
        ]);
    }

    public function show(Request $request, $ref)
    {
        
        $transaction = Transaction::where('reference_id', $ref)->first();

        if (!$transaction) {
            return $this->notFoundResponse();
        }

        return $this->success(['transaction' => $transaction]);
    }

    public function credit(Request $request, $account_no)
    {
        $validator = $this->handleValidation([
            'channel' => 'required',
            'narration' => 'required',
            'type' => 'required',
            'date' => ['required', 'date', 
                function ($attribute, $value, $fail) {
                    $today = date('Y-m-d');
                    $today = date('Y-m-d', strtotime($today));
                    $date = date('Y-m-d', strtotime($value));
                    if (strtotime($date) < strtotime($today)) {
                        $fail('Please the earliest ' .$attribute .' is ' . $today . '.');
                    }
                }
            ],
            'amount' => 'required|numeric',
        ]);
    
        if ($validator){ 
        
            return $validator;
        }

        $customer = $this->customerRepo->findWithAccountNo($account_no);

        if (!$customer) {
            return $this->notFoundResponse('Account details not found');
        }

        if($customer->status != $customer::ACTIVE_STATUS){

            return $this->error('Account is '. $customer->status, 403);
        }

        try{
            DB::beginTransaction();

            $transaction = CustomerTransactionService::credit($customer, $request->amount, $request->narration, $request->type, $request->date, $request->channel);

            DB::commit();           
        
        }catch(\Exception $e){
            DB::rollback();           
            return $this->error($e->getMessage(), 403);
        }

        return $this->success([
            'transaction' => $transaction, 
            'account_balance' => $customer->account_balance,
            'msg' => 'Account successfully credited']);
    }

    public function debit(Request $request, $account_no)
    {
        $validator = $this->handleValidation([
            'channel' => 'required',
            'narration' => 'required',
            'type' => 'required',
            'date' => ['required', 'date', 
                function ($attribute, $value, $fail) {
                    $today = date('Y-m-d');
                    $today = date('Y-m-d', strtotime($today));
                    $date = date('Y-m-d', strtotime($value));
                    if (strtotime($date) < strtotime($today)) {
                        $fail('Please the earliest ' .$attribute .' is ' . $today . '.');
                    }
                }
            ],
            'amount' => 'required|numeric',
        ]);
    
        if ($validator){ 
        
            return $validator;
        }

        $customer = $this->customerRepo->findWithAccountNo($account_no);

        if (!$customer) {
            return $this->notFoundResponse('Account details not found');
        }

        if($customer->status != $customer::ACTIVE_STATUS){

            return $this->error('Account is '. $customer->status, 403);
        }


        if(!$customer->canDebit($request->amount)){

            return $this->error('Account balance is too low.', 403);
        }


        try{
            DB::beginTransaction();

            $transaction = CustomerTransactionService::debit($customer, $request->amount, $request->narration, $request->type, $request->date, $request->channel);

            DB::commit();           
        
        }catch(\Exception $e){
            DB::rollback();           
            return $this->error($e->getMessage(), 403);
        }

        return $this->success([
                'transaction' => $transaction, 
                'account_balance' => $customer->account_balance,
                'msg' => 'Account successfully debited']);
    }

}
