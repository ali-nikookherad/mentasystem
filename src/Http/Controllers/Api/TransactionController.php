<?php

namespace Modules\Wallet\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Account\Entities\Account;
use Modules\Wallet\Entities\Order;
use Modules\Wallet\Entities\Transaction;
use Modules\Wallet\Http\Requests\TransactionStoreRequest;
use Modules\Wallet\repo\TransactionDB;
use Modules\Wallet\Transformers\TransactionResource;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return mixed
     */
    public function index()
    {
        $limit = \request()->has("limit") ? \request()->input("limit") : 5;
        $transaction = Transaction::paginate($limit);
        $resource = TransactionResource::collection($transaction);
        return $resource;
    }

    /**
     * Store a newly created resource in storage.
     * @param TransactionDB $repository
     * @param TransactionStoreRequest $request
     * @return mixed
     */
    public function store(TransactionStoreRequest $request, TransactionDB $repository)
    {
        $data = $request->all();
        $instance = $repository->insert($data);
        if ($instance instanceof Transaction) {
            return \response()
                ->json([
                    "message" => "successfully insert",
                    "data" => $instance
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
     * @return mixed
     */
    public function show($id)
    {
        $transaction = Transaction::find($id);
        $resource = new TransactionResource($transaction);
        return $resource;
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
     * @return mixed
     */
    public function destroy($id)
    {
        Transaction::where("id", $id)->delete();
        return \response()
            ->json([
            ], 204);
    }

    public function cashout(Request $request)
    {
        $account = Account::where("id", $request->input("account_id"))->with(["credit", "account"])->first();
        $treasury = $account->credit->treasury;
        if ($treasury < $request->input("amount")) {
            return \response()
                ->json([
                    "message" => "you dont have enough inventory"
                ], 400);
        }

        \DB::transaction(function () use ($account, $request) {
            $transaction = new TransactionDB();
            $amount = $request->input("amount");
            $treasuryAccount = $account->account->credit->treasury;

            //update treasury account value
            $account->account->credit->update(["treasury" => ($treasuryAccount + $amount)]);

            //update account treasury value
            $account->credit->update(["treasury" => ($treasuryAccount - $amount)]);

            //TODO create repo
            //submit order
            $order = Order::create([
                "goods_id" => $request->input("goods_id"),
                "from_account_id" => $account->id,
                "to_account_id" => $account->account->id,
                "amount" => $amount,
                "refund" => false,
                "cashout" => true,
                "treasury_account_id" => $account->account->id
            ]);

            //create transaction
            $data = [
                "order_id" => $order->id,
                "account_id" => $account->id,
                "amount" => $amount,
                "cashout" => true,
                "type" => "low_off",
            ];
            $transaction->insert($data);

            //create transaction
            $data = [
                "order_id" => $order->id,
                "account_id" => $account->account->id,
                "amount" => $amount,
                "cashout" => true,
                "type" => "add",
            ];
            $transaction->insert($data);

        });

        return \response()
            ->json([
                "message" => "successfully cash out"
            ], 200);
    }
}
