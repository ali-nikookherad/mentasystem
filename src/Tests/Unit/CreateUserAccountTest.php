<?php

namespace Tests\Unit;

use Mentasystem\Wallet\Http\Controllers\Api\AccountController;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;


class CreateUserAccountTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testExample()
    {
        $accountController=new AccountController();
        $data=[
            "user_id"=>600,
            "user_type"=>"merchant",
        ];
        $request = new Request();
        $request->replace($data);

        $response=$accountController->store($request);
        $response->assertStatus(200);
    }
}
