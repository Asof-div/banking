<?php

use Illuminate\Database\Seeder;
use App\Repos\CustomerRepository;

class AccountTableSeeder extends Seeder
{
    public function __construct(CustomerRepository $customerRepo){
        $this->customerRepo = $customerRepo;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('customers')->truncate();
        DB::table('customer_balances')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->create([
            'last_name' => 'Babalola',
            'first_name' => 'Tunde',
            'full_name' => 'Tunde Babalola',
            'account_name' => 'Tunde Babalola',
            'account_type' => 'Saving',
            'email' => 'tunde@gmail.com',
            'currency' => 'NGN',
        ]);


        $this->create([
            'last_name' => 'Emeka',
            'first_name' => 'Godwin',
            'full_name' => 'Emeka Godwin',
            'account_name' => 'Emeka Godwin',
            'account_type' => 'Current',
            'currency' => 'GBP',
        ]);
    }

    function create($params)
    {
        
        $bvns = $this->customerRepo->all([])->pluck('bvn');
        $bvn = $this->generate($bvns);
        $params['bvn'] = $bvn;    
        $params['status'] = '';    
        
        $customer = $this->customerRepo->create($params);
    }

    function generate($bvns, $size=11){

        $code = substr( sprintf("%02d", rand(0000, 9999). time() ) , 0, $size);
        
        if($bvns->search($code)){
            return $this->generate($bvns);
        }
        return $code;
    }

}
