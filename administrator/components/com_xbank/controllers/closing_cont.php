<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * mod_closings/all_closing Controller : module to run closing for bank branchwise.
 */
class closing_cont extends CI_Controller {
//    function closing_cont() {
//        set_time_limit(5000);
//    }

    /**
     * default function to be called when closing is triggered
     *
     * STEPS
     * checks whether the closings table has the record for the current branch
     * if closing is run for the first time for the current branch, a record for this branch is inseted in the table
     * closing starts with daily closing and proceeds in the following order- daily, weekly, monthly, half-yearly, yearly
     */
    function index() {
        set_time_limit(5000);
        $cl = new xConfig("closing");
        $closingMode = $cl->getKey("Closing_mode");
        if ($closingMode) {
            $branches = Branch::getAllBranches();
            foreach ($branches as $b) {
                $this->dailyclosing($b);
            }
        } else {
            $this->dailyclosing(Branch::getCurrentBranch());
            }
        }

        function dailyclosing($b) {
        global $com_params;
        if ($b->PerformClosings == 1) {
            $staff = $this->db->query("select StaffID,Password from jos_xstaff where branch_id = $b->id and AccessLevel = '" . BRANCH_ADMIN . "' limit 1")->row();
//            $this->encryptLogin($b->id);
            $q = new Closing();
            $q->where("branch_id", $b->id)->get();
            $q = $q->result_count();
            if ($q == 0) {
                $query = "insert into jos_xclosings(branch_id) values(" . $b->id . ")";
                executeQuery($query);
            }

            try {
                $this->db->trans_begin();
// call daily
                $dateToday = getNow("Y-m-d");
                $dailyDate = $this->db->query("select daily as Daily from jos_xclosings where branch_id=" . $b->id)->row()->Daily;

                if ($dailyDate == "" or $dailyDate == "0000-00-00 00:00:00") {
                    $accounttypes = explode(",", $com_params->get('ACCOUNT_TYPES'));
                    foreach ($accounttypes as $acctype) {
                        include(xBANKSCHEMEPATH . "/" . strtolower($acctype) . "/" . strtolower($acctype) . "daily.php");
                    }
                    $i = $dateToday;
                    $this->updateDaily($b);
                    $this->other_closings($i, $b);
                } elseif ($dailyDate < $dateToday) {
                    $dailyDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($dailyDate)) . " +1 day"));
                    for ($i = $dailyDate; $i <= $dateToday;) {
                        $accounttypes = explode(",", $com_params->get('ACCOUNT_TYPES'));
                        foreach ($accounttypes as $acctype) {
                            include(xBANKSCHEMEPATH . "/" . strtolower($acctype) . "/" . strtolower($acctype) . "daily.php");
                        }
// check for other closings here
                        $this->updateDaily($b);
                        $this->other_closings($i, $b);

                        $i = date("Y-m-d", strtotime(date("Y-m-d", strtotime($i)) . " +1 day")); //strtotime(date("Y-m-d", strtotime($dailyDate)) . " +1 day");
                    }
                } else {
                    echo "You are running closing for $dateToday <br/>You cannot run closing on backdate. Last closing done on <b>$dailyDate</b>";
                }

                $this->db->trans_commit();
                log_message('error', "Closing done on $dateToday");
                echo "<br/>Done";
            } catch (Exception $e) {
                $this->db->trans_rollback();
                echo $e->getMessage();
                return;
            }
        }
    }

    function other_closings($dateToday, $b) {
        global $com_params;
        if (date("j", strtotime($dateToday)) == 1) {
//                   call all monthly
            $accounttypes = explode(",", $com_params->get('ACCOUNT_TYPES'));
            foreach ($accounttypes as $acctype) {
                include(xBANKSCHEMEPATH . "/" . strtolower($acctype) . "/" . strtolower($acctype) . "monthly.php");
            }
            if ($b->id == 100)
                $this->forSalaryTransfer($b);
            $this->updateMonthly($b);
        }

//                    check for halfyearly to run or not
        if ((date("j", strtotime($dateToday)) == 1 and date("n", strtotime($dateToday)) == 10) || (date("j", strtotime($dateToday)) == 1 and date("n", strtotime($dateToday)) == 4)) {
//                    call all halfyearly
            $accounttypes = explode(",", $com_params->get('ACCOUNT_TYPES'));
            foreach ($accounttypes as $acctype) {
                include(xBANKSCHEMEPATH . "/" . strtolower($acctype) . "/" . strtolower($acctype) . "halfyearly.php");
            }
            $this->updateHalfYearly($b);
        }

//                    check for yearly to run or not
        if (date("j", strtotime($dateToday)) == 1 and date("n", strtotime($dateToday)) == 4) {
//                    call or yearly
            $accounttypes = explode(",", $com_params->get('ACCOUNT_TYPES'));
            foreach ($accounttypes as $acctype) {
                include(xBANKSCHEMEPATH . "/" . strtolower($acctype) . "/" . strtolower($acctype) . "yearly.php");
            }
            $this->updateYearly($b);
        }
    }

    /**
     *  Update table closings, set daily closing date to getNow()
     */
    function updateDaily($b) {
        $q = "update `jos_xclosings` as `c` set `c`.`daily` ='" . getNow("Y-m-d") . "' where `c`.`branch_id`=" . $b->id;
        executeQuery($q);
//			echo $q;
    }

    /**
     *  Update table closings, set weekly closing date to getNow()
     */
    function updateWeekly($b) {
        $q = "update `jos_xclosings` as `c` set `c`.`weekly` ='" . getNow("Y-m-d") . "' where `c`.`branch_id`=" . $b->id;
        executeQuery($q);
//        echo $q;
    }

    /**
     *  Update table closings, set monthly closing date to getNow()
     */
    function updateMonthly($b) {
        $q = "update `jos_xclosings` as `c` set `c`.`monthly` ='" . getNow("Y-m-d") . "' where `c`.`branch_id`=" . $b->id;
        executeQuery($q);
//        echo $q;
    }

    /**
     *  Update table closings, set half-yearly closing date to getNow()
     */
    function updateHalfYearly($b) {
        $q = "update `jos_xclosings` as `c` set `c`.`halfyearly` ='" . getNow("Y-m-d") . "' where `c`.`branch_id`=" . $b->id;
        executeQuery($q);
//        echo $q;
    }

    /**
     *  Update table closings, set yearly closing date to getNow()
     */
    function updateYearly($b) {
        $q = "update `jos_xclosings` as `c` set `c`.`yearly` ='" . getNow("Y-m-d") . "' where `c`.`branch_id`=" . $b->id;
        executeQuery($q);
//        echo $q;
    }

    function forSalaryTransfer($b) {
        $staff = Doctrine::getTable('Staff')->findByBranch_id($b->id);
        foreach ($staff as $s) {
            if ($s->AccessLevel >= 80)
                continue;
            $workingDays = 0;
            $q = Doctrine_Query::create()
                            ->select("Count(*) AS workingDays")
                            ->from("StaffAttendance")
                            ->where("`Attendance` = '" . LEAVE . "' and `staff_id`=$s->id and `Date`>=DATE_ADD('" . getNow("Y-m-d") . "',INTERVAL -1 MONTH) and `Date` < '" . getNow("Y-m-d") . "'");

            $leaves = $q->execute()->getFirst()->workingDays;
            $sd = Doctrine::getTable("StaffDetails")->findOneByStaff_id($s->id);
            if ($sd) {
                $account = Doctrine::getTable("Accounts")->findOneById($sd->SavingAccount);
                if ($account && $account->branch_id == $b->id) {
                    $firstDayOfLastMonth = date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 MONTH"));

                    $i = $firstDayOfLastMonth;
                    for ($j = 1; $j <= 30;) {
                        if ($sd->JoiningDate > $i)
                            continue;
                        $bankHoliday = Doctrine::getTable("BankHolidays")->findOneByHolidaydate($i);

//apply all conditions
                        if ($bankHoliday && $leaves <= 3)
                            $workingDays++;
                        if (date("w", strtotime($i)) == 0 && $leaves <= 3)
                            $workingDays++;

                        $staffAttendance = Doctrine::getTable("StaffAttendance")->findOneByDateAndStaff_id($i, $s->id);
                        if ($staffAttendance && $staffAttendance->Attendance == PRESENT)
                            $workingDays++;
                        if ($staffAttendance && $staffAttendance->Attendance == LEAVE && $leaves <= 3)
                            $workingDays++;


                        $i = date("Y-m-d", strtotime(date("Y-m-d", strtotime($i)) . " +1 day"));
//					echo "date : ".$i."<br>";
                    }


                    $salary = $workingDays * ($sd->BasicPay / 30);
                    $debitAccount = array(
                        $b->Code . SP . CASH_ACCOUNT => $salary,
                    );
                    $creditAccount = array(
                        $account->AccountNumber => $salary,
                    );
                    Transactions::doTransaction($debitAccount, $creditAccount, "Salary Amount Deposited in $account->AccountNumber", TRA_SAVING_ACCOUNT_AMOUNT_DEPOSIT, Transactions::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));

                    $sp = new StaffPayments();
                    $sp->Date = getNow("Y-m-d");
                    $sp->Payment = $salary;
                    $sp->PaymentAgainst = date("n", strtotime(getNow("Y-m-d"))) - 1;
                    $sp->staff_id = $s->id;
                    $sp->save();

//            $sp = $this->db->query("insert into staff_payments(`Date`,Payment,PaymentAgainst,staff_id) values('".getNow("Y-m-d")."',$salary,'". date("n", strtotime(getNow("Y-m-d"))) - 1 ."',$s->id)");
                }
            }
        }
    }

    function forDepriciationCalculation($b) {
        $b = $b;
        $q = Doctrine_Query::create()
                        ->select("s.*")
                        ->from("Schemes s")
                        ->where("s.isDepriciable = 1");
        $schemes = $q->execute();

        foreach ($schemes as $sc) {
            $query = Doctrine_Query::create()
                            ->select("a.*")
                            ->from("Accounts a")
                            ->where("a.schemes_id = $sc->id AND a.ActiveStatus=1 and a.created_at < '" . getNow("Y-m-d") . "' and a.branch_id=" . $b->id);
            $accounts = $query->execute();

            if ($accounts->count() == 0)
                continue;
            foreach ($accounts as $a) {
                if ($a->created_at > date("Y-m-d", strtotime(date("Y-m-d", strtotime((date("Y", strtotime(getNow())) - 1) . "-09-30"))))) {
                    $depr = $sc->DepriciationPercentAfterSep;
                } else {
                    $depr = $sc->DepriciationPercentBeforeSep;
                }

                $depAmt = ($a->CurrentBalanceDr - $a->CurrentBalanceCr) * $depr / 100;
//                    echo $depAmt;
                $debitAccount = array(
                    $b->Code . SP . DEPRECIATION_ON_FIXED_ASSETS => round($depAmt)
                );
                $creditAccount = array(
                    $a->AccountNumber => round($depAmt)
                );

                Transactions::doTransaction($debitAccount, $creditAccount, "Depriciation amount calculated", TRA_DEPRICIATION_AMOUNT_CALCULATED, Transactions::getNewVoucherNumber(), date("Y-m-d", strtotime(date("Y-m-d", strtotime(getNow("Y-m-d"))) . " -1 day")));
            }
        }
    }

    function encryptLogin($branch='') {
        $b = new Branch($branch);
        $m = Branch::getDefaultMember($b);
        $u = JFactory::getUser($m->netmember_id);
        global $mainframe;
        jimport('joomla.user.helper');

        $db = & JFactory::getDBO();
        $query = 'SELECT `id`, `password`, `gid`'
                . ' FROM `#__users`'
                . ' WHERE username=' . $db->Quote($u->username)
                . '   AND password=' . $db->Quote($u->password)
        ;
        $db->setQuery($query);
        $result = $db->loadObject();

        if ($result) {
            JPluginHelper::importPlugin('user');

            $response->username = $u->username;
            $result = $mainframe->triggerEvent('onLoginUser', array((array) $response, $options));
        }

        // if OK go to redirect page
//        if ($this->params->get('urlredirect')) {
//        if ($result) {
//            $mainframe->redirect("index.php?option=com_xbank&task=accounts_cont.test");
//        }
//        }

        return;
    }

}

?>