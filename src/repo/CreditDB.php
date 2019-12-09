<?php
/**
 * Created by PhpStorm.
 * User: a_nikookherad
 * Date: 9/17/19
 * Time: 10:17 AM
 */

namespace Mentasystem\Wallet\repo;


use Mentasystem\Wallet\Entities\Credit;

class CreditDB
{

    /**
     * @param $date
     * @return bool
     */
    public function create($date)
    {
        $credit = Credit::create($date);
        if ($credit instanceof Credit) {
            return $credit;
        }
        return false;
    }

    /**
     * @param $account_id
     * @param $date
     * @return bool
     */
    public function update($account_id,$date)
    {
        $credit = Credit::where("account_id",$account_id)->update($date);
        if ($credit instanceof Credit) {
            return $credit;
        }
        return false;
    }
}
