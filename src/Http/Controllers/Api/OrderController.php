<?php

namespace Mentasystem\Wallet\Http\Controllers\Api;

use DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mentasystem\Wallet\Entities\Order;
use Mentasystem\Wallet\repo\OrderDB;
use Mentasystem\Wallet\repo\TransactionDB;

/**
 * @property Order refund
 */
class OrderController extends Controller
{
    private $refund;

    /**
     * @return mixed
     * @throws \Exception
     */
    public function index()
    {
        $orderDB = new OrderDB();
        list($page, $limit, $user_id, $product_id, $wallet, $fromAmount, $toAmount, $from, $to, $day, $week, $month) = $this->getRequestInputs();

        //get all orders by pagination
        $orders = $orderDB->getList($limit, $user_id, $product_id, $wallet, $fromAmount, $toAmount, $from, $to, $day, $week, $month);

        if (!$orders) {
            return response()
                ->json([
                    "message" => "some things went wrong",
                    "error" => "your collection is empty"
                ], 400);
        }
        return response()
            ->json([
                "message" => "user all orders",
                "data" => $orders
            ], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $orderDB = new OrderDB();
        $data = $request->all();

        $instance = $orderDB->create($data);
        if ($instance) {
            return $instance;
        }

        return response()
            ->json([
                "message" => "we can not create order",
                "error" => "some things went wrong"
            ], 400);
    }

    /**
     * @param $mobile
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($mobile)
    {
        //get user with him orders
        $userDB = new UserDB();
        list($page, $limit, $from, $to, $day, $week, $month) = $this->getRequestInputs();

        $resource = $userDB->getUserProductOrder($mobile, $from, $to, $day, $week, $month);
        if (empty($resource)) {
            return response()
                ->json([
                    "message" => "some things went wrong",
                    "error" => "there is no order",
                ], 400);
        }

        //convert resource data to pagination
        $response = $resource->paginate($limit, $page);
        return response()
            ->json([
                "message" => "user orders information",
                "data" => $response
            ], 200);
    }

    /**
     * @param Request $request
     * @param $id
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        Order::where("id", $id)->delete();
        return \response()
            ->json([
                null
            ], 204);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function refund(Request $request)
    {
        $order = Order::where(["id" => $request->order_id, "refund" => false])->with("transactions")->first();
        if (!($order instanceof Order)) {
            return \response()
                ->json([
                    "message" => "your order is not found or refund before",
                    "error" => "not found data"
                ], 404);
        }

        /*-----------refund transaction---------------*/
        DB::transaction(function () use ($order) {
            $transaction = new TransactionDB();
            Order::where("id", $order->id)->update(["refund" => true]);

            //first transaction
            $data = [
                "order_id" => $order->id,
                "account_id" => $order->from_account_id,
                "amount" => $order->amount,
                "type" => "add",
                "reverse" => 1,
            ];
            $transaction->insert($data);

            //second transaction
            $data['account_id'] = $order->to_account_id;
            $data['type'] = "low_off";
            $transaction->insert($data);

            $from = Account::find($order->from_account_id);
            $to = Account::find($order->to_account_id);

            //update from account credit
            $old_credit = $from->credit->toArray();
            $new_treasury = ($old_credit["treasury"]) + ($order->amount);
            $from->credit->update([
                "treasury" => $new_treasury,
            ]);

            //update to account credit
            $old_credit = $to->credit->toArray();
            $old_treasury = ($old_credit["treasury"]) - ($order->amount);
            $to->credit->update([
                "treasury" => $old_treasury,
            ]);

            $this->refund = $order;
        });

        return \response()
            ->json([
                "message" => "success refund order",
                "data" => $this->refund
            ], 200);
    }

    /**
     * @return array
     */
    private function getRequestInputs(): array
    {
        $page = \request()->has("page") ? \request("page") : 1;
        $limit = \request()->has("limit") ? \request("limit") : 10;
        $user_id = \request()->has("user_id") ? \request("user_id") : null;
        $product_id = \request()->has("product_id") ? \request("product_id") : null;
        $wallet = \request()->has("wallet") ? \request("wallet") : null;
        $fromAmount = \request()->has("from_amount") ? \request("from_amount") : null;
        $toAmount = \request()->has("to_amount") ? \request("to_amount") : null;
        $from = \request()->has("from") ? \request("from") : null;
        $to = \request()->has("to") ? \request("to") : null;
        $day = \request()->has("day") ? \request("day") : null;
        $week = \request()->has("week") ? \request("week") : null;
        $month = \request()->has("month") ? \request("month") : null;
        return array($page, $limit, $user_id, $product_id, $wallet, $fromAmount, $toAmount, $from, $to, $day, $week, $month);
    }


}
