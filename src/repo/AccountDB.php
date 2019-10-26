<?php


namespace Mentasystem\Wallet\repo;

use Illuminate\Support\Facades\DB;
use Mentasystem\Wallet\Entities\Account;

/**
 * Class AccountDB
 * @package Modules\Wallet\repo
 */
class AccountDB
{

    /**
     * insert account to database
     * @param  $data
     * @return mixed
     */
    public function create($data)
    {
        $instance = Account::create($data);
        if ($instance instanceof Account) {
            return $instance;
        }
        return false;
    }

    /**
     * @param $id
     * @return bool|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function get($id)
    {
        $response = Account::where("id", $id)->with(["credits", "accountType", "user"])->first();
        if ($response instanceof Account) {
            return $response;
        }
        return false;
    }

    /**
     * @param $id
     * @param bool $withCredit
     * @param bool $withUser
     * @return bool|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Account|Account[]|object|null
     */
    public function getAccount($id, $withCredit = false, $withUser = false)
    {
        if ($withCredit) {
            $account = Account::where("id", $id)->with(["credits", "accountType"])->first();
            if ($account instanceof Account) {
                return $account;
            }
        }

        if ($withUser) {
            $account = Account::where('id', $id)->with(['user', 'accountType'])->first();
            if ($account instanceof Account) {
                return $account;
            }
        }

        if (!$withCredit && !$withUser) {
            $account = Account::find($id);
            if ($account instanceof Account) {
                return $account;
            }
        }

        return false;
    }

    /**
     * @param $wallet_id
     * @return bool|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Account|Account[]|object|null
     */
    public function getTreasuryAccount($wallet_id)
    {
        $treasuryId = \DB::table("wallets")
            ->where("wallets.id", $wallet_id)
            ->join("account_types as types", "wallets.id", "=", "types.wallet_id")
            ->where("types.type", "treasury")
            ->join("accounts", "types.id", "=", "accounts.account_type_id")
            ->select("accounts.id")
            ->first();
        return $this->getAccount($treasuryId->id);
    }

    /**
     * @param $user_id
     * @param $treasury_id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getUserAccount($user_id, $treasury_id = null)
    {
        if (isset($treasury_id)) {
            $instance = Account::where("user_id", "=", $user_id)->where("treasury_id", $treasury_id)->first();
        } else {
            $instance = Account::where("user_id", "=", $user_id)->get();
        }

        return $instance;
    }

    /**
     * @param $id
     * @return \Illuminate\Support\Collection
     */
    public function getAccountTreasuryType($id)
    {
        $accountTreasuryType = \DB::table("accounts")
            ->where("id", $id)
            ->join("account_types as type", "accounts.account_type_id", "type.id")
            ->join("wallets", "type.wallet_id", "=", "wallets.id")
            ->select("wallets.type")
            ->get();
        return $accountTreasuryType;
    }

    /**
     * @param $limit
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getAccounts($limit)
    {
        $accounts = Account::paginate($limit);
//        $resource = GetAccountInfoResource::collection($accounts);
        return $accounts;
    }


    /**
     * @param $id
     * @throws \Exception
     */
    public function deleteAccount($user_id)
    {
        try{
            DB::beginTransaction();
            $accounts = Account::where("user_id",$user_id)->with("credits")->get();
            foreach ($accounts as $account) {
                $credits=$account->credits;
                foreach ($credits as $credit) {
                    $credit->delete();
                }
                $account->delete();
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw new \Exception($e->getMessage(),400);
        }
    }

    /**
     * @param $account_id
     * @param null $wallet_id
     * @return mixed
     */
    public function getAccountCredit($account_id, $wallet_id = null)
    {
        $accountInstance = Account::where("id", $account_id)->with("credits")->first();
        $creditInstance = $accountInstance->credits->first();
        return $creditInstance;
    }

}
