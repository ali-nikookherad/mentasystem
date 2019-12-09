<?php

namespace Modules\Wallet\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Wallet\repo\CreditDB;

class CreditController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $creditDB = new CreditDB();
        $data = [
            "amount" => $request->input("amount"),
            "treasury_id" => $request->input("treasury_id")
        ];
        $instance = $creditDB->create($data);

        //check for create credit
        if ($instance) {
            return \response()
                ->json([
                    "message" => "credit successfully created"
                ], 200);
        }
        return \response()
            ->json([
                "message" => "some things went wrong",
                "error" => "bad request"
            ], 400);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
