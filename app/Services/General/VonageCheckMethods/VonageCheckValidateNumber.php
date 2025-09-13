<?php 
namespace App\Services\General\VonageCheckMethods;

class VonageCheckValidateNumber
{
    public function checkPhoneNumberValidity($phoneNumber)
    {
        $basic = new \Vonage\Client\Credentials\Basic(config('services.vonage.key'), config('services.vonage.pass'));
        $client = new \Vonage\Client($basic);
        try {
            // Make a Number Validation request
              $response = $client->insights()->basic($phoneNumber)->toArray();
              return ['message' => 'success','data'=>$response];
        } catch (\Vonage\Client\Exception\Request $e) {
            return ['message' => '','data'=>$e->getMessage()];
        }
    }
}
