<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\APIResponseTrait;
use App\Repos\CustomerRepository;
use App\Repos\TransactionRepository;
use App\Models\Transaction;
use App\Services\CustomerTransactionService;
use DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    use APIResponseTrait;

    protected static $modelName = 'Transaction';

    public function __construct(CustomerRepository $customerRepo, TransactionRepository $transactionRepo){
        $this->customerRepo = $customerRepo;
        $this->transactionRepo = $transactionRepo;
    }

    public function index(Request $request){        

        $transactions = $this->transactionRepo->all($request->all());

        return $this->success([
            'transactions' => $transactions,
        ]);
    }

    public function custom(Request $request, $account_no){

        $customer = $this->customerRepo->findWithAccountNo($account_no);

        if (!$customer) {
            return $this->notFoundResponse('Account details not found');
        }

        $transactions = $this->transactionRepo->all($request->all());
        
        return $this->success([
            'transactions' => $transactions,
        ]);
    }

    public function show(Request $request, $account_no, $ref)
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
            
            $transaction = CustomerTransactionService::creditAction($customer, $request->amount, $request->narration, $request->type, $request->date, $request->channel);

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

    public function atm(Request $request, $account_no)
    {
        $validator = $this->handleValidation([
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

        $charge = CustomerTransactionService::atmCharge($customer, $request->amount);

        if(!$customer->canDebit($request->amount + $charge)){

            return $this->error('Account balance is too low.', 403);
        }


        try{
            DB::beginTransaction();

            $transaction = CustomerTransactionService::atmDebit($customer, $request->amount, $request->narration, $request->type, $request->date);

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

    public function pos(Request $request, $account_no)
    {
        $validator = $this->handleValidation([
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

        $charge = CustomerTransactionService::posCharge($customer, $request->amount);

        if(!$customer->canDebit($request->amount + $charge)){

            return $this->error('Account balance is too low.', 403);
        }


        try{
            DB::beginTransaction();

            $transaction = CustomerTransactionService::posDebit($customer, $request->amount, $request->narration, $request->type, $request->date);

            DB::commit();           
        
        }catch(\Exception $e){
            DB::rollback();           
            \Log::info($e);

            return $this->error($e->getMessage(), 403);
        }

        return $this->success([
                'transaction' => $transaction, 
                'account_balance' => $customer->account_balance,
                'msg' => 'Account successfully debited']);
    }


    public function etransfer(Request $request, $a_account_no, $b_account_no)
    {
        $validator = $this->handleValidation([
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

        $a_customer = $this->customerRepo->findWithAccountNo($a_account_no);
        $b_customer = $this->customerRepo->findWithAccountNo($b_account_no);

        if (!$a_customer) {
            return $this->notFoundResponse('Account details A not found');
        }

        if (!$b_customer) {
            return $this->notFoundResponse('Account details B not found');
        }

        if($a_customer->status != $a_customer::ACTIVE_STATUS){

            return $this->error('Account A is '. $a_customer->status, 403);
        }

        if($b_customer->status != $b_customer::ACTIVE_STATUS){

            return $this->error('Account B is '. $b_customer->status, 403);
        }

        $charge = CustomerTransactionService::eChannelCharge($a_customer, $request->amount);

        if(!$a_customer->canDebit($request->amount + $charge)){

            return $this->error('Account balance is too low.', 403);
        }


        try{
            DB::beginTransaction();

            $transaction = CustomerTransactionService::eChannelTransfer($a_customer, $b_customer, $request->amount, $request->narration, $request->type, $request->date);

            DB::commit();           
        
        }catch(\Exception $e){
            DB::rollback();           
            return $this->error($e->getMessage(), 403);
        }

        return $this->success([
                'transaction' => $transaction, 
                'a_account_balance' => $a_customer->account_balance,
                'b_account_balance' => $b_customer->account_balance,
                'msg' => 'Account successfully debited']);
    }
}
