<?php
/**
 * Created by PhpStorm.
 * User: a_nikookherad
 * Date: 9/7/19
 * Time: 12:26 PM
 */

namespace Mentasystem\Wallet\repo;

use Carbon\Carbon;
use Mentasystem\Wallet\Entities\Account;
use Mentasystem\Wallet\Entities\Order;
use Mentasystem\Wallet\Entities\Wallet;
use Mentasystem\Wallet\Events\CreatedOrderEvent;
use Mentasystem\Wallet\Jobs\CreateTransactionJob;
use Mentasystem\Wallet\Transformers\OrderResource;

/**
 * Class OrderDB
 * @package Mentasystem\Wallet\repo
 */
class OrderDB
{
    /**
     * @param $limit
     * @param $user_id
     * @param $product_id
     * @param $wallet
     * @param $fromAmount
     * @param $toAmount
     * @param $from
     * @param $to
     * @param $day
     * @param $week
     * @param $month
     * @return mixed
     * @throws \Exception
     */
    public function getList($limit, $user_id, $product_id, $wallet, $fromAmount, $toAmount, $from, $to, $day, $week, $month)
    {
        $filter = [
            ["paid_at", "!=", null]
        ];

        //product filter
        if (!is_null($product_id)) {
            foreach ($product_id as $key) {
                array_push($filter, ["goods_id", "=", $key]);
            }
        }

        //wallet filter
        if (!is_null($wallet)) {
            $treasuryAccountInstance = $this->walletToTreasuryAcc($wallet);
            if (!$treasuryAccountInstance instanceof Account) {
                throw new \Exception("your filter is not correct! your wallet does not exist...",400);
                /*                return request()
                                    ->json([
                                        "message" => "your filter is not correct",
                                        "error" => "wallet does not exist",
                                    ], 400);*/
            }
            array_push($filter, ["treasury_account_id", "=", $treasuryAccountInstance->id]);
        }

        //time filter
        if (isset($from)) {
            $from = date("Y-m-d" . " 00:00:00", strtotime($from));
            $to = date("Y-m-d" . " 00:00:00", strtotime($to));
            array_push($filter, ["created_at", ">=", $from], ["created_at", "<=", $to]);
        } elseif (isset($day) || isset($week) || isset($month)) {
            if (isset($day)) {
                array_push($filter, ["created_at", ">=", Carbon::now()->subDay($day)]);
            } elseif (isset($week)) {
                array_push($filter, ["created_at", ">=", Carbon::now()->subWeek($week)]);
            } else {
                array_push($filter, ["created_at", ">=", Carbon::now()->subMonth($month)]);
            }
        }

        // user filter
        if (!is_null($user_id)) {
            array_push($filter, ["from_account_id", "=", $user_id]);
        }

        // amount filter
        if (!is_null($fromAmount)) {
            array_push($filter, ["amount", ">=", $fromAmount]);
        }

        if (!is_null($toAmount)) {
            array_push($filter, ["amount", "<=", $toAmount]);
        }

        //inquiry of order
        $ordersInstance = Order::
        where($filter)
            ->with("transactions")
            ->paginate($limit);
//            ->get();

        return $ordersInstance;
    }

    /**
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function create($request)
    {
        $accountDB = new AccountDB();
        $from_user_id = $request["from_user_id"];
        $to_user_id = $request["to_user_id"];
        $amount = $request["amount"];
        $product_id = $request["product_id"];
        $walletDB = new WalletDB();
        $result = [];
        try {
            \DB::beginTransaction();

            foreach ($amount as $item => $value) {

                //get database wallet type
                $databaseWallet = $walletDB->get($item);

                //if input wallet type is wrong
                if (!$databaseWallet instanceof Wallet) {
                    return response()
                        ->json([
                            "message" => "some things went wrong",
                            "error" => "your wallet type is not exist"
                        ], 400);
                }

                //submit rials wallet_order
                $treasuryAccountInstance = $accountDB->getTreasuryAccount($databaseWallet->id);

                //get user (account) with wallet type
                $fromUserRialAccount = $accountDB->getUserAccount($from_user_id, $treasuryAccountInstance->id);
                $toUserRialAccount = $accountDB->getUserAccount($to_user_id, $treasuryAccountInstance->id);

                /*----------------------submit wallet order----------------------*/

                $amount = $value;
                $request["amount"] = $amount;



                if (!($treasuryAccountInstance instanceof Account)) {
                    return response()
                        ->json([
                            "message" => "some things went wrong",
                            "error" => "your {$databaseWallet->type} treasury account does not exist"
                        ], 400);

                }

                //create wallet order

                $orderInstance = Order::create([
                    "goods_id" => $product_id,
                    "from_account_id" => $fromUserRialAccount->id,
                    "to_account_id" => $toUserRialAccount->id,
                    "amount" => $amount,
                    "type" => "request",
                    "paid_at" => null,
                    "treasury_account_id" => $treasuryAccountInstance->id,
                ]);
                array_push($result, $orderInstance->toArray());

                //call job to create transaction
                CreateTransactionJob::dispatchNow(
                    $orderInstance,
                    $request,
                    $fromUserRialAccount,
                    $toUserRialAccount,
                    $treasuryAccountInstance
                );
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()
                ->json([
                    "message" => "some thing went wrong",
                    "error" => "your order is not created"
                ], 400);
        }
        return response()
            ->json([
                "message" => "order submit successfully",
                "data" => $result
            ], 200);
    }

    /**
     * @param $wallet
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Modules\Wallet\Entities\Account|object|null
     */
    private function walletToTreasuryAcc($wallet)
    {
        $response = Account::whereHas('accountType', function ($q) {
            $q->where("type", "=", "treasury");
        })->whereHas('accountType.wallet', function ($q) use ($wallet) {
            $q->where("title", "=", $wallet);
        })->first();
        return $response;
    }
}
