<?php

namespace Mentasystem\Wallet\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mentasystem\Wallet\Entities\AccountType;
use Mentasystem\Wallet\Entities\Wallet;
use Mentasystem\Wallet\Entities\Account;
use Mentasystem\Wallet\repo\CreditDB;
use Mentasystem\Wallet\repo\AccountDB;
use Mentasystem\Wallet\repo\AccountTypeDB;

class AccountController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $repository = new AccountDB();
        $limit = request()->has("limit") ? request()->input("limit") : 10;
        $response = $repository->getAccounts($limit);
        return response()
            ->json([
                "message" => "account list",
                "data" => $response
            ], 200);
    }

    /**
     * @param AccountDB $repository
     * @param $id
     * @return bool|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Account|Account[]|object|null
     */
    public function show(AccountDB $repository, $id)
    {
        $account = $repository->getAccount($id);
        return $account;
        /*        $account = $repository->getAccount($id);
                return new getAccountInfoResource($account);*/
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $accountDB = new AccountDB();
        $accountTypeDB = new AccountTypeDB();
        $creditDB = new CreditDB();
        $user_id = $request->user_id;
        $user_type = $request->user_type;

        try {
            \DB::beginTransaction();

            //get account type
            $accountTypes=$accountTypeDB->getAccountTypeWithAccountType($user_type);

            //create account for every treasury account type
            foreach ($accountTypes as $accountType) {

                //get treasury account
                $treasuryAccountInstance = $accountDB->getTreasuryAccount($accountType->wallet_id);


                //create account
                $accountData = [
                    "user_id" => $user_id,
                    "treasury_id" => $treasuryAccountInstance->id,
                    "account_type_id" => $accountType->id,
                ];
                $accountInstance = $accountDB->create($accountData);

                //check for successfully create account
                if (!$accountInstance) {
                    return \response()
                        ->json([
                            'message' => __("messages.account_create_fail"),
                            "error" => __("messages.account_create_fail")
                        ], 409);
                }

                //create credit
                $creditData = [
                    "account_id" => $accountInstance->id,
                    "treasury_id" => $treasuryAccountInstance->id,
                    "amount" => 0,
                    "usable_at" => null,
                    "expired_at" => null,
                    "revoked" => false,
                ];
                $creditDB->create($creditData);
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            //failed response before create account
            return response()
                ->json([
                    "message" => "some things went wrong",
                    "error" => $e->getMessage(),
                ], 400);
        }

        //success response after create account
        return response()
            ->json([
                "message" => "account successfully created"
            ], 200);
    }

    /**
     * @param Requset $request
     * @param AccountDB $repository
     * @param $id
     * @return mixed
     */
    public function update(Requset $request, AccountDB $repository, $id)
    {
        $data = $repository
            ->convertRequestToArray($request);
        $account = Account::where('id', $id)
            ->update($data);
        return $account;
    }

    /**
     * @param AccountDB $repository
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $accountDB=new AccountDB();
        $accountDB->deleteAccount($id);
        return \response()
            ->json([
                null
            ], 204);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function charge(Request $request)
    {
        $accountDB = new AccountDB();

        try {
            /*----------charge account credit-----------*/
            \DB::beginTransaction();
            $user_id = $request->has("user_id") ? \request("user_id") : null;
            $wallets = $request->has("wallets") ? \request("wallets") : null;
            if (!$user_id) {
                return response()
                    ->json([
                        "message" => "some things went wrong",
                        "message" => "please enter user id",
                    ], 400);
            }

            foreach ($wallets as $wallet => $value) {

                //convert wallet to treasury id
                //get wallet id
                $walletInstance = Wallet::where("type", "=", $wallet)->first();

                //get account type
                $accountTypeInstance = AccountType::where([["wallet_id", "=", $walletInstance->id], ["type", "=", "treasury"]])->first();

                //get user accounts
                $treasuryAccountInstance = Account::where([["user_id", "=", null], ["treasury_id" => null], ["account_type_id", "=", $accountTypeInstance->id]])->first();

                //get account
                $accountInstance = Account::where([["user_id", "=", $user_id], ["treasury_id", "=", $treasuryAccountInstance->id]])->with("credits")->first();

                //get credit
                $creditInstance = $accountInstance->credits->first();
                $oldAmount = $creditInstance->amount;
                $creditInstance->update([
                    "amount" => $value + $oldAmount
                ]);
                $creditInstance->save();
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()
                ->json([
                    "message" => "some thing went wrong"
                ], 400);
        }

        return response()
            ->json([
                "message" => "account successfully charged"
            ], 200);
    }
}
