<?php

namespace Mentasystem\Wallet\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mentasystem\Wallet\Entities\Account;
use Mentasystem\Wallet\Entities\AccountType;
use Mentasystem\Wallet\Entities\Credit;
use Mentasystem\Wallet\repo\AccountDB;
use Mentasystem\Wallet\repo\AccountTypeDB;
use Mentasystem\Wallet\repo\CreditDB;
use Mentasystem\Wallet\repo\WalletDB;
use Mentasystem\Wallet\Entities\Wallet;

class WalletController extends Controller
{
    /**
     * @param Request $request
     * @param WalletDB $walletDB
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(Request $request, WalletDB $walletDB)
    {
        $data = $request->all();
        $accountDB = new AccountDB();
        $accountTypeDB = new AccountTypeDB();
        $creditDB = new CreditDB();

        try {
            \DB::beginTransaction();

            //create wallet (wallet type)
            $walletInstance = $walletDB->create($data);

            //after create wallet event(create two treasury account)
//            event(new WalletCreatedEvent($walletInstance, $data));

                //create account type for treasury
                $accountTypeData = [
                    "type" => "treasury",
                    "wallet_id" => $walletInstance->id,
                    "title" => isset($data["title"]) ? $data["title"] : null,
                    "subtitle" => isset($data["subtitle"]) ? $data["subtitle"] : null,
                    "description" => isset($data["description"]) ? $data["description"] : null,
                    "balance_type" => "ziro",
                    "min_account_amount" => 0,
                    "max_account_amount" => 0,
                    "min_transaction_amount" => 0,
                    "max_transaction_amount" => 0,
                    "legal" => false,
                    "interest_rate" => 1,
                    "interest_period" => 1,
                    "revoked" => false,
                ];
                $instanceTypeAccount = $accountTypeDB->create($accountTypeData);

                //create account for treasury
                $accountData = [
                    "account_type_id" => $instanceTypeAccount->id,
                    "user_id" => null,
//                    "min_transaction" => 0,
                    "revoked" => false,
                ];
                $instanceAccount = $accountDB->create($accountData);

                //treasury account credit
                $creditData = [
                    "account_id" => $instanceAccount->id,
//                    "wallet_id" => $walletInstance->id,
                    "treasury_id" => 0,
                    "club_id" => isset($data["club_id"]) ? $data["club_id"] : 0,
                    "amount" => 0,
                    "usable_at" => null,
                    "expired_at" => null,
                    "revoked" => false,
                ];
                $creditDB->create($creditData);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return \response()
                ->json([
                    "message" => "some things went wrong",
                    "error" => "{$e->getMessage()}"
                ], 400);
        }

        //check for create wallet
        if ($walletInstance) {
            return \response()
                ->json([
                    "message" => "wallet successfully created",
                    "data"=>$walletInstance
                ], 200);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $walletDB = new WalletDB();
        $response = $walletDB->list();
        return response()
            ->json([
                "message" => "wallet list",
                "data" => $response
            ], 200);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        return Wallet::where("id", $id)->get();
    }

    /**
     * @param Request $r
     * @param $id
     * @param WalletDB $walletDB
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $r, $id, WalletDB $walletDB)
    {
        $data = $r->all();
        $walletInstance = $walletDB->find($id);
        if (is_null($walletInstance)) {
            return response()
                ->json('id (' . $id . ') dose not exist', 400);
        }
        if ($walletDB->update($id, $data)) {
            return response()->json($walletDB->find($id), 201);
        } else {
            return Response()
                ->json([
                    'message' => "wallet can't be updated, update it again"
                ], 400);
        }
    }

    /**
     * @param $id
     * @param WalletDB $walletDB
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy($id, WalletDB $walletDB)
    {
        event(new WalletDeletedEvent($id));
        if (is_null($walletDB->find($id))) return response()->json(['id (' . $id . ') dose not exist']);
        try {
            \DB::beginTransaction();
            //delete wallet (wallet type)
            $walletDB->delete($id);


            //after delete wallet delete [accType, acc, credit]
            $accTypeId = AccountType::select("id")->where("wallet_id", $id)->get()->toArray()[0]["id"];
            AccountType::where("wallet_id", $id)->delete();
            Account::where("account_type_id", $accTypeId)->delete();
            credit::where("account_id", $accTypeId)->delete();

            \DB::commit();
            return response()
                ->json('', 204);
        } catch (\Exception $e) {
            \DB::rollBack();
            return \response()
                ->json([
                    "message" => "some things went wrong",
                    "error" => "{$e->getMessage()}"
                ], 400);
        }
    }

    /**
     * @param Request $r
     * @return \Illuminate\Http\JsonResponse
     */
    public function walletCr6663edit(Request $r)
    {
        $idUser = auth()->user()["id"];
        $account = Account::where('user_id', $idUser)
            ->with(['credits', 'accountType'])->whereHas('accountType', function ($q) {
                $q->with("wallet");
            })->get();
        $walletTitle = [];
        $creditInfo = [];
        foreach ($account->toArray() as $key) {
            $credit = $key["credits"][0];
            $walletTitle = Wallet::where("id", $key["account_type"]["id"])->get()->toArray()[0]["title"];
            $Arr = ["id" => $credit["id"],
                "club_id" => $credit["club_id"],
                "amount" => $credit["amount"],
                "type" => $walletTitle,
                "expired_at" => $credit["expired_at"],
                "usable_at" => $credit["usable_at"]];

            array_push($creditInfo, $Arr);
        };

        $walletTitle = $account[0]->accountType->wallet->toArray()["title"];
        return Response()->json([
            $creditInfo
        ], 200);
    }
}
