<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateUserAccountTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $data=[
            "user_id"=>600,
            "user_type"=>"merchant"
        ];

        $headers =[
            "Content-Type"=>"application/json",
            "Accept"=>"application/json",
        ];

        $url ="wallet/accounts";

        $response=$this->withHeaders($headers)->json("POST",$url,$data);
        $response->assertStatus(200);
    }
}
