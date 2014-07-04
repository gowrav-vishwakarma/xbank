<?php

class balancesheet_cont extends CI_Controller {
    function index() {

    }

    function balanceSheetForm() {
        $form = $this->form->open( "accountdetails", "index.php?option=com_xbank&task=balancesheet_cont.getBalanceSheet" )
        ->setColumns( 3 )
        ->dateBox( "From", "name='fromDate' class='input'" )
        ->dateBox( "Till", "name='toDate' class='input'" )
        ->submit( "Go" );
        return $this->form->get();
    }

    function pandlSheetForm() {
        $form = $this->form->open( "accountdetails", "index.php?option=com_xbank&task=balancesheet_cont.getPandL" )
        ->setColumns( 3 )
        ->dateBox( "From", "name='fromDate' class='input'" )
        ->dateBox( "Till", "name='toDate' class='input'" )
        ->submit( "Go" );
        return $this->form->get();
    }

    function pandlSheetFormNew() {
        $form = $this->form->open( "accountdetails", "index.php?option=com_xbank&task=balancesheet_cont.getPandLNew" )
        ->setColumns( 3 )
        ->dateBox( "From", "name='fromDate' class='input'" )
        ->dateBox( "Till", "name='toDate' class='input'" )
        ->submit( "Go" );
        return $this->form->get();
    }

    function getBalanceSheet( $fromDate=null, $toDate=null, $branch=null ) {
        $arr=array();
        if ( inp( 'BranchId' ) !='' and inp( 'BranchId' ) !='%' )
            $branch=inp( 'BranchId' );
        if ( $branch == null ) $branch= Branch::getCurrentBranch()->id;

        if ( inp( 'BranchId' )=='%' ) $branch=null;

        $toDate = getNow( 'Y-m-d' );
        $fromDate = '1970-01-01';
        if ( inp( 'fromDate' ) )
            $fromDate=inp( 'fromDate' );
        if ( inp( 'toDate' ) )
            $toDate = inp( 'toDate' );

        $this->session->set_userdata( "fromdate", $fromDate );
        $this->session->set_userdata( "todate", $toDate );
        $this->session->set_userdata( "branch", $branch );

        xDeveloperToolBars::onlyCancel( "report_cont.balanceSheetForm", "cancel", "BalanceSheet " . date( 'd-M-Y', strtotime( $fromDate ) ) . " To " . date( 'd-M-Y', strtotime( $toDate ) ) );

        $data['balancesteet']=array();
        $heads=new BalanceSheet();
        $heads->where( 'is_pandl', 0 );
        $heads->order_by('order');
        $heads->get();

        $LT_SUM=0;
        $RT_SUM=0;
        $PANDL = $this->getPandLClosingValue( $fromDate, $toDate, $branch );

        foreach ( $heads as $h ) {
            $clbs = $h->getClosingBalance( $toDate, $branch );
            foreach ( $clbs as $clb ) {
                $subtract_from = "amount".$h->subtract_from;
                $subtract_this = "amount".( $h->subtract_from == 'Dr' ? 'Cr': 'Dr' );
                $subDetails = $h->show_sub;
                $subFunction = "get".$subDetails."ViseClosingBalance";
                $temp_data=array( 'Total'=>$clb, 'Detailed'=>$h->{$subFunction}( $toDate, $branch ) );
                if ( ( $amt=( $clb->$subtract_from - $clb->$subtract_this ) ) >= 0 ) {
                    $data['balancesteet'][$h->positive_side][] = $temp_data;
                    ${$h->positive_side."_SUM"} += abs( round($amt,2) );
                }
                else {
                    $data['balancesteet'][( $h->positive_side == 'LT' ? 'RT' : 'LT' )][] = $temp_data;
                    ${( $h->positive_side == 'LT' ? 'RT' : 'LT' )."_SUM"} += abs( round($amt,2) );
                }

            }
        }

        if ( ( $PANDL->amountDr - $PANDL->amountCr ) < 0 ) {
            $data['balancesteet']['LT'][]=array( 'Total'=>$PANDL, 'Detailed'=>array() );
            $LT_SUM += abs( round($PANDL->amountDr - $PANDL->amountCr,2) );
        }else {
            $data['balancesteet']['RT'][]=array( 'Total'=>$PANDL, 'Detailed'=>array() );
            $RT_SUM += abs( round($PANDL->amountDr - $PANDL->amountCr,2) );
        }

        // Calculate and put runtime suspence
        if ( $LT_SUM != $RT_SUM ) {
            $arr = array(
                'Msg' => "<font color='red'>Human Entry Error found</font>",
                'Title' => 'Msg',
                'amountDr' => $LT_SUM,
                'amountCr' => $RT_SUM,
                'Head' => 'Error',
                'SubtractFrom' => 'Dr'
            );
        }

        $run_error=arrayToObject( $arr );

        if ( abs( $LT_SUM - $RT_SUM ) > 1 ) {
            if ( $LT_SUM < $RT_SUM ) {
                $data['balancesteet']['LT'][]=array( 'Total'=>$run_error, 'Detailed'=>array() );
                $LT_SUM += abs( $run_error->amountDr - $run_error->amountCr );
            }else {
                $data['balancesteet']['RT'][]=array( 'Total'=>$run_error, 'Detailed'=>array() );
                $RT_SUM += abs( $run_error->amountDr - $run_error->amountCr );
            }
        }

        $data['LT']=$data['RT']="";
        $data['LT_SUM']=$LT_SUM;
        $data['RT_SUM']=$RT_SUM;

        foreach ( $data['balancesteet']['LT'] as $viewdata ) {
            JRequest::setVar( "layout", "head_row" );
            $data['LT'] .= $this->load->view( 'balancesheet.html', $viewdata, true );
        }

        foreach ( $data['balancesteet']['RT'] as $viewdata ) {
            JRequest::setVar( "layout", "head_row" );
            $data['RT'] .= $this->load->view( 'balancesheet.html', $viewdata, true );
        }

        $data['form']="";//$this->balanceSheetForm();

        JRequest::setVar( "layout", "balancesheet" );
        $this->load->view( 'balancesheet.html', $data );
        $this->jq->getHeader();
    }

    function getPandL( $fromDate=null, $toDate=null, $branch=null ) {

        $toDate = getNow( 'Y-m-d' );
        $fromDate = '1970-01-01';
        if ( inp( 'fromDate' ) )
            $fromDate=inp( 'fromDate' );
        if ( inp( 'toDate' ) )
            $toDate = inp( 'toDate' );

        if ( $branch==null and Branch::getCurrentBranch()->Code != 'DFL' ) $branch= Branch::getCurrentBranch()->id;

        $this->session->set_userdata( "fromdate", $fromDate );
        $this->session->set_userdata( "todate", $toDate );
        $this->session->set_userdata( "branch", $branch );

        $data['balancesteet']=array();
        $heads=new BalanceSheet();
        $heads->where( 'is_pandl', 1 );
        $heads->get();

        $LT_SUM=0;
        $RT_SUM=0;

        foreach ( $heads as $h ) {
            $clbs = $h->getClosingBalance( $toDate, $branch, null, true, $fromDate );
            foreach ( $clbs as $clb ) {
                $subtract_from = "amount".$h->subtract_from;
                $subtract_this = "amount".( $h->subtract_from == 'Dr' ? 'Cr': 'Dr' );
                $subDetails = $h->show_sub;
                $subFunction = "get".$subDetails."ViseClosingBalance";
                $temp_data=array( 'Total'=>$clb, 'Detailed'=>$h->{$subFunction}( $toDate, $branch, null, true, $fromDate ) );
                if ( ( $amt=( $clb->$subtract_from - $clb->$subtract_this ) ) >= 0 ) {
                    $data['balancesteet'][$h->positive_side][] = $temp_data;
                    ${$h->positive_side."_SUM"} += abs( $amt );
                }
                else {
                    $data['balancesteet'][( $h->positive_side == 'LT' ? 'RT' : 'LT' )][] = $temp_data;
                    ${( $h->positive_side == 'LT' ? 'RT' : 'LT' )."_SUM"} += abs( $amt );
                }

            }
        }


        $loss_profit=new stdClass;
        if ( ( $LT_SUM - $RT_SUM ) < 0 ) {
            $loss_profit->Title = "Profit";
            $loss_profit->Profit = "Profit";
            $loss_profit->amountCr = $RT_SUM;
            $loss_profit->amountDr = $LT_SUM;
            $data['balancesteet']['LT'][]=array( 'Total'=>$loss_profit, 'Detailed'=>array() );
            $LT_SUM += abs( $LT_SUM - $RT_SUM );
        }else {
            $loss_profit->Title = "Loss";
            $loss_profit->Loss = "Loss";
            $loss_profit->amountCr = $RT_SUM;
            $loss_profit->amountDr = $LT_SUM;
            $data['balancesteet']['RT'][]=array( 'Total'=>$loss_profit, 'Detailed'=>array() );
            $RT_SUM += abs( $RT_SUM - $LT_SUM );
        }

        $data['LT']=$data['RT']="";
        $data['LT_SUM']=$LT_SUM;
        $data['RT_SUM']=$RT_SUM;


        foreach ( $data['balancesteet']['LT'] as $viewdata ) {
            $viewdata['is_pandl']=1;
            JRequest::setVar( "layout", "head_row" );
            $data['LT'] .= $this->load->view( 'balancesheet.html', $viewdata, true );
        }

        foreach ( $data['balancesteet']['RT'] as $viewdata ) {
            $viewdata['is_pandl']=1;
            JRequest::setVar( "layout", "head_row" );
            $data['RT'] .= $this->load->view( 'balancesheet.html', $viewdata, true );
        }

        $data['form']=$this->pandlSheetForm();
        $data['report_name']="Profit and Loss  Sheet";
        JRequest::setVar( "layout", "balancesheet" );
        $this->load->view( 'balancesheet.html', $data );
        $this->jq->getHeader();
    }

    function getPandLNew( $fromDate=null, $toDate=null, $branch=null ) {

        $toDate = getNow( 'Y-m-d' );
        $fromDate = '1970-01-01';
        if ( inp( 'fromDate' ) )
            $fromDate=inp( 'fromDate' );
        if ( inp( 'toDate' ) )
            $toDate = inp( 'toDate' );

        if ( $branch==null and Branch::getCurrentBranch()->Code != 'DFL' ) $branch= Branch::getCurrentBranch()->id;

        $this->session->set_userdata( "fromdate", $fromDate );
        $this->session->set_userdata( "todate", $toDate );
        $this->session->set_userdata( "branch", $branch );

        $data['balancesteet']=array();
        $heads=new BalanceSheet();
        $heads->where( 'is_pandl', 1 );
        $heads->get();

        $LT_SUM=0;
        $RT_SUM=0;

        foreach ( $heads as $h ) {
            $clbs = $h->getClosingBalance( $toDate, $branch, null, true, $fromDate );
            foreach ( $clbs as $clb ) {
                $subtract_from = "amount".$h->subtract_from;
                $subtract_this = "amount".( $h->subtract_from == 'Dr' ? 'Cr': 'Dr' );
                $subDetails = $h->show_sub;
                $subFunction = "getGroupedAccountsViseClosingBalance";
                $temp_data=array( 'Total'=>$clb, 'Detailed'=>$h->{$subFunction}( $toDate, $branch, null, true, $fromDate ) );
                if ( ( $amt=( $clb->$subtract_from - $clb->$subtract_this ) ) >= 0 ) {
                    $data['balancesteet'][$h->positive_side][] = $temp_data;
                    ${$h->positive_side."_SUM"} += abs( $amt );
                }
                else {
                    $data['balancesteet'][( $h->positive_side == 'LT' ? 'RT' : 'LT' )][] = $temp_data;
                    ${( $h->positive_side == 'LT' ? 'RT' : 'LT' )."_SUM"} += abs( $amt );
                }

            }
        }


        $loss_profit=new stdClass;
        if ( ( $LT_SUM - $RT_SUM ) < 0 ) {
            $loss_profit->Title = "Profit";
            $loss_profit->Profit = "Profit";
            $loss_profit->amountCr = $RT_SUM;
            $loss_profit->amountDr = $LT_SUM;
            $data['balancesteet']['LT'][]=array( 'Total'=>$loss_profit, 'Detailed'=>array() );
            $LT_SUM += abs( $LT_SUM - $RT_SUM );
        }else {
            $loss_profit->Title = "Loss";
            $loss_profit->Loss = "Loss";
            $loss_profit->amountCr = $RT_SUM;
            $loss_profit->amountDr = $LT_SUM;
            $data['balancesteet']['RT'][]=array( 'Total'=>$loss_profit, 'Detailed'=>array() );
            $RT_SUM += abs( $RT_SUM - $LT_SUM );
        }

        $data['LT']=$data['RT']="";
        $data['LT_SUM']=$LT_SUM;
        $data['RT_SUM']=$RT_SUM;


        foreach ( $data['balancesteet']['LT'] as $viewdata ) {
            $viewdata['is_pandl']=1;
            JRequest::setVar( "layout", "head_row" );
            $data['LT'] .= $this->load->view( 'balancesheet.html', $viewdata, true );
        }

        foreach ( $data['balancesteet']['RT'] as $viewdata ) {
            $viewdata['is_pandl']=1;
            JRequest::setVar( "layout", "head_row" );
            $data['RT'] .= $this->load->view( 'balancesheet.html', $viewdata, true );
        }

        $data['form']=$this->pandlSheetFormNew();
        $data['report_name']="Profit and Loss  Sheet";
        JRequest::setVar( "layout", "balancesheet" );
        $this->load->view( 'balancesheet.html', $data );
        $this->jq->getHeader();
    }

    function getPandLClosingValue( $dateFrom=null, $dateOn=null, $branch=null ) {


        $opt= new stdClass;
        $opt->amountCr=0;
        $opt->amountDr=0;
        // $month = date('m',strtotime($dateFrom));
        // $year=date('Y',strtotime($dateFrom));
        // if($month>=1 and $month <=3){
        //     $year--;
        // }
        // $financialYearStart = "$year-04-01";

        // $opt=new Transaction();
        // $opt->select("SUM(amountDr) as amountDr, SUM(amountCr) as amountCr");
        // $opt->include_related('account/scheme/balancesheet','Head');
        // $opt->include_related('account/scheme/balancesheet','subtract_from');
        // $opt->where("created_at >=",$financialYearStart);
        // $opt->where("created_at <",$dateFrom);
        // $opt->where_related("account/scheme/balancesheet","is_pandl",1);
        // if($branch!='')
        //     $opt->where("branch_id",$branch);
        // $opt->group_start();
        // $opt->where_related("account","ActiveStatus","1");
        // $opt->or_where_related("account","affectsBalanceSheet","1");
        // $opt->group_end();
        // $opt->get();

        $dateOn = date( "Y-m-d", strtotime( date( "Y-m-d", strtotime( $dateOn ) ) . " +1 DAY" ) );
        $t=new Transaction();
        $t->select( "SUM(amountDr) as amountDr, SUM(amountCr) as amountCr" );
        $t->include_related( 'account/scheme/balancesheet', 'Head' );
        $t->include_related( 'account/scheme/balancesheet', 'subtract_from' );
        $t->where( "created_at >=", $dateFrom );
        $t->where( "created_at <", $dateOn );
        $t->where_related( "account/scheme/balancesheet", "is_pandl", 1 );
        if ( $branch!='' )
            $t->where( "branch_id", $branch );
        $t->group_start();
        $t->where_related( "account", "ActiveStatus", "1" );
        $t->or_where_related( "account", "affectsBalanceSheet", "1" );
        $t->group_end();
        $t->get();

        if ( ( ( $t->amountDr + $opt->amountDr ) - ( $t->amountCr + $opt->amountCr ) ) < 0 )
            $title="Net Profit";
        else
            $title="Net Loss";

        $arr = array(
            'PandL' => $title,
            'Title' => 'PandL',
            'amountDr' => $t->amountDr + $opt->amountDr,
            'amountCr' => $t->amountCr + $opt->amountCr,
            'Head' => 'aaa',
            'SubtractFrom' => $t->account_scheme_balancesheet_subtract_from

        );
        return arrayToObject( $arr );

    }

    function getTrialBalance($fromDate=null, $toDate=null, $branch=null) {
        $arr=array();
        if ( inp( 'BranchId' ) !='' and inp( 'BranchId' ) !='%' )
            $branch=inp( 'BranchId' );
        if ( $branch == null ) $branch= Branch::getCurrentBranch()->id;

        if ( inp( 'BranchId' )=='%' ) $branch=null;

        $toDate = getNow( 'Y-m-d' );
        $fromDate = '1970-01-01';
        if ( inp( 'fromDate' ) )
            $fromDate=inp( 'fromDate' );
        if ( inp( 'toDate' ) )
            $toDate = inp( 'toDate' );

        $this->session->set_userdata( "fromdate", $fromDate );
        $this->session->set_userdata( "todate", $toDate );
        $this->session->set_userdata( "branch", $branch );

        xDeveloperToolBars::onlyCancel( "report_cont.balanceSheetForm", "cancel", "BalanceSheet " . date( 'd-M-Y', strtotime( $fromDate ) ) . " To " . date( 'd-M-Y', strtotime( $toDate ) ) );

        $data['balancesteet']=array();
        $heads=new BalanceSheet();
        // $heads->where( 'is_pandl', 0 );
        $heads->order_by('order');
        $heads->get();

        $Dr_SUM=0;
        $Cr_SUM=0;
        //$this->getPandLClosingValue( $fromDate, $toDate, $branch );

        $trial_data = array();
        $data['head_rows']="";

        $dr_sum = $cr_sum = 0;


        foreach ( $heads as $h ) {
            $clbs = $h->getClosingBalance( $toDate, $branch );
            foreach ( $clbs as $clb ) {
                
                $subtract_from = "amount".$h->subtract_from;
                $subtract_this = "amount".( $h->subtract_from == 'Dr' ? 'Cr': 'Dr' );
                $subDetails = $h->show_sub;
                $subFunction = "get".$subDetails."ViseClosingBalance";

                $trial_data = array( 'Total'=>$clb,'subtract_from'=>$h->subtract_from, 'is_pandl'=> $h->is_pandl, 'Detailed'=>$h->{$subFunction}( $toDate, $branch ) );

                if($h->subtract_from == 'Dr'){
                    if($h->is_pandl)
                        $cr_sum += $clb->amountCr - $clb->amountDr;
                    else
                        $dr_sum += $clb->amountDr - $clb->amountCr;

                }else{
                    if($h->is_pandl)
                        $dr_sum += $clb->amountDr - $clb->amountCr;
                    else
                        $cr_sum += $clb->amountCr - $clb->amountDr;
                }

                JRequest::setVar( "layout", "trial_head_row" );
                $data['head_rows'] .= $this->load->view( 'balancesheet.html', $trial_data, true );

            }
        }

        $data['form']="";//$this->balanceSheetForm();
        $data['totals'] = array('dr'=>$dr_sum,'cr'=>$cr_sum);

        $data['report_name'] = "Trial Balance" ;
        JRequest::setVar( "layout", "trialbalance" );
        $this->load->view( 'balancesheet.html', $data );
        $this->jq->getHeader();

        return;


        echo "<pre>";
        print_r($trial_data);
        echo "</pre>";
        return;
        // if ( ( $PANDL->amountDr - $PANDL->amountCr ) < 0 ) {
        //     $data['balancesteet']['Dr'][]=array( 'Total'=>$PANDL, 'Detailed'=>array() );
        //     $LT_SUM += abs( $PANDL->amountDr - $PANDL->amountCr );
        // }else {
        //     $data['balancesteet']['Cr'][]=array( 'Total'=>$PANDL, 'Detailed'=>array() );
        //     $RT_SUM += abs( $PANDL->amountDr - $PANDL->amountCr );
        // }

        // Calculate and put runtime suspence
        if ( $Dr_SUM != $Cr_SUM ) {
            $arr = array(
                'Msg' => "<font color='red'>Human Entry Error found</font>",
                'Title' => 'Msg',
                'amountDr' => $Dr_SUM,
                'amountCr' => $Cr_SUM,
                'Head' => 'Error',
                'SubtractFrom' => 'Dr'
            );
        }

        $run_error=arrayToObject( $arr );

        if ( abs( $Dr_SUM - $Cr_SUM ) > 1 ) {
            if ( $Dr_SUM < $Cr_SUM ) {
                $data['balancesteet']['LT'][]=array( 'Total'=>$run_error, 'Detailed'=>array() );
                $Dr_SUM += abs( $run_error->amountDr - $run_error->amountCr );
            }else {
                $data['balancesteet']['RT'][]=array( 'Total'=>$run_error, 'Detailed'=>array() );
                $Cr_SUM += abs( $run_error->amountDr - $run_error->amountCr );
            }
        }

        // $data['Dr']=$data['Cr']="";
        // $data['Dr_SUM']=$LT_SUM;
        // $data['Cr_SUM']=$RT_SUM;

        // foreach ( $data['balancesteet']['Dr'] as $viewdata ) {
        //     JRequest::setVar( "layout", "head_row" );
        //     $data['Dr'] .= $this->load->view( 'balancesheet.html', $viewdata, true );
        // }

        // foreach ( $data['balancesteet']['Cr'] as $viewdata ) {
        //     JRequest::setVar( "layout", "head_row" );
        //     $data['Cr'] .= $this->load->view( 'balancesheet.html', $viewdata, true );
        // }

        $data['form']="";//$this->balanceSheetForm();
        $data['report_name'] = "Trial Balance";
        JRequest::setVar( "layout", "trialbalance" );
        $this->load->view( 'balancesheet.html', $data );
        $this->jq->getHeader();
    


    }

    function digin() {
        $func="digin".inp( 'digtype' );
        $branch=$this->session->userdata( 'branch' );
        $this->$func( $branch );

    }

    function diginSchemeType( $branch=null ) {
        $dateOn = date( "Y-m-d", strtotime( date( "Y-m-d", strtotime( $this->session->userdata( 'todate' ) ) ) . " +1 DAY" ) );
        $t=new Transaction();
        $t->select( "SUM(amountDr) as amountDr, SUM(amountCr) as amountCr" );
        $t->include_related( 'account/scheme/balancesheet', 'subtract_from' );
        $t->include_related( 'account/scheme', 'Name' );
        $t->where( "created_at <", $dateOn );
        $t->where_related( "account/scheme", "SchemeType", urldecode( inp( 'digid' ) ) );
        if ( $branch!=null )
            $t->where( "branch_id", $branch );
        $t->group_start();
        $t->where_related( "account", "ActiveStatus", "1" );
        $t->or_where_related( "account", "affectsBalanceSheet", "1" );
        $t->group_end();
        $t->group_by( 'account_scheme_Name' );
        $t->get();

        $a=new Account();
        $a->select( 'SUM(OpeningBalanceDr) as OpeningBalanceDr' );
        $a->select( 'SUM(OpeningBalanceCr) as OpeningBalanceCr' );
        $a->include_related( 'scheme', 'Name' );
        $a->include_related( 'scheme/balancesheet', 'Head' );
        $a->include_related( 'scheme/balancesheet', 'subtract_from' );
        if ( $branch!=null )
            $a->where( "branch_id", $branch );
        $a->group_start();
        $a->where( "ActiveStatus", "1" );
        $a->or_where( "affectsBalanceSheet", "1" );
        $a->group_end();
        $a->where_related( 'scheme', 'SchemeType', urldecode( inp( 'digid' ) ) );
        $a->group_by( 'scheme_Name' );
        $a->get();
        // echo $a->check_last_query();

        $arr=array();

        $schemename_found_in_tr=array();
        foreach ( $t as $tt ) {
            foreach ( $a as $aa )
                if ( $aa->scheme_Name === $tt->account_scheme_Name ) {
                    $Dr=$tt->amountDr + $aa->OpeningBalanceDr;
                    $Cr=$tt->amountCr + $aa->OpeningBalanceCr;
                    $schemename_found_in_tr[] = $aa->scheme_Name;
                }
            $amount= ${$tt->account_scheme_balancesheet_subtract_from} - ${( $tt->account_scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' )};
            if ( $amount!=0 ) {
                $arr[] = array(
                    'SchemeName' => $tt->account_scheme_Name,
                    'SchemeNameURL' => urlencode( $tt->account_scheme_Name ),
                    'Title' => 'SchemeName',
                    'amountDr' => $Dr,
                    'amountCr' => $Cr,
                    'amount' => $amount,
                    'side' => ( $amount > 0 ? $tt->account_scheme_balancesheet_subtract_from : ( $tt->account_scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' ) ),
                    'Head' => $tt->account_scheme_balancesheet_Head,
                    'SubtractFrom' => $tt->account_scheme_balancesheet_subtract_from
                );
            }
        }

        foreach ( $a as $aa ) {
            if ( array_search( $aa->scheme_Name, $schemename_found_in_tr )===false ) {
                $Dr=$aa->OpeningBalanceDr;
                $Cr=$aa->OpeningBalanceCr;
                $amount= ${$aa->scheme_balancesheet_subtract_from} - ${( $aa->scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' )};
                if ( $amount !=0 ) {
                    $arr[] = array(
                        'SchemeName' => ( $aa->scheme_Name ),
                        'SchemeNameURL' => urlencode( $aa->scheme_Name ),
                        'Title' => 'SchemeType',
                        'amountDr' => $aa->OpeningBalanceDr,
                        'amountCr' => $aa->OpeningBalanceCr,
                        'amount' => $amount,
                        'side' => ( $amount > 0 ? $aa->scheme_balancesheet_subtract_from : ( $aa->scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' ) ),
                        'Head' => $aa->scheme_balancesheet_Head,
                        'SubtractFrom' => $aa->scheme_balancesheet_subtract_from
                    );
                }
            }
        }


        $data['report'] = getReporttable( arrayToObject( $arr ),             //model
            array( "Scheme Name", 'amount', 'side' ),       //heads
            array( 'SchemeName', 'amount', 'side' ),       //fields
            array( 'amount' ),        //totals_array
            array(),        //headers
            array( 'sno'=>true ),     //options
            "header",     //headerTemplate
            '',      //tableFooterTemplate
            "",      //footerTemplate,
            array( 'SchemeName'=>array(
                    'task'=>'balancesheet_cont.digin',
                    'class'=>'alertinwindow',
                    'title'=>'_blank',
                    'url_post'=>array( 'digtype'=>'"SchemeName"', 'format'=>'"raw"', 'digid'=>'#SchemeNameURL' )
                )
            )//Links array('field'=>array('task'=>,'class'=>''))
        );

        JRequest::setVar( "layout", "generalreport" );
        $this->load->view( 'report.html', $data );
        $this->jq->getHeader();

    }

    function diginSchemeName( $branch=null ) {
        $fromDate=$this->session->userdata( 'fromdate' );
        $toDate=$this->session->userdata( 'todate' );

        $is_pandl=false;
        if ( inp( 'pandl' ) ) {
            $month = date( 'm', strtotime( $fromDate ) );
            $year=date( 'Y', strtotime( $fromDate ) );
            if ( $month>=1 and $month <=3 ) {
                $year--;
            }
            $financialYearStart = "$year-04-01";
            // echo $financialYearStart . "<br/>";
            $is_pandl=true;
        }


        $dateOn = date( "Y-m-d", strtotime( date( "Y-m-d", strtotime( $toDate ) ) . " +1 DAY" ) );
        $t=new Transaction();
        $t->select( "SUM(amountDr) as amountDr, SUM(amountCr) as amountCr" );
        $t->include_related( 'account/scheme/balancesheet', 'subtract_from' );
        $t->include_related( 'account', 'AccountNumber' );
        if ( $is_pandl ) {
            $t->where( 'created_at >=', $financialYearStart );
        }
        $t->where( "created_at <", $dateOn );
        $t->where_related( "account/scheme", "Name like '%".str_replace( " ", "%", urldecode( inp( 'digid' ) ) ). "%'" );
        if ( $branch!=null )
            $t->where( "branch_id", $branch );
        $t->group_start();
        $t->where_related( "account", "ActiveStatus", "1" );
        $t->or_where_related( "account", "affectsBalanceSheet", "1" );
        $t->group_end();
        $t->group_by( 'account_AccountNumber' );
        $t->get();

        // echo $t->check_last_query();

        // Accounts without Transactions
        $arr=array();

        $a=new Account();
        $a->select( 'OpeningBalanceDr' );
        $a->select( 'OpeningBalanceCr' );
        $a->select( 'AccountNumber' );
        $a->include_related( 'scheme/balancesheet', 'Head' );
        $a->include_related( 'scheme/balancesheet', 'subtract_from' );
        if ( $branch!=null )
            $a->where( "branch_id", $branch );
        $a->group_start();
        $a->where( "ActiveStatus", "1" );
        $a->or_where( "affectsBalanceSheet", "1" );
        $a->group_end();
        $a->where_related( 'scheme', 'Name', urldecode( inp( 'digid' ) ) );
        // $a->group_by('scheme_Name');
        $a->get();

        $account_found_in_tr = array();
        foreach ( $t as $tt ) {
            $Dr=$tt->amountDr;
            $Cr=$tt->amountCr;
            foreach ( $a as $aa ) {
                if ( $aa->AccountNumber == $tt->account_AccountNumber ) {
                    $Dr= $tt->amountDr + $aa->OpeningBalanceDr;
                    $Cr= $tt->amountCr + $aa->OpeningBalanceCr;
                    $account_found_in_tr[] = $aa->AccountNumber;
                }
            }
            // $Dr = $tt->amountDr + $tt->account_OpeningBalanceDr;
            // $Cr = $tt->amountCr + $tt->account_OpeningBalanceCr;
            $amount= ${$tt->account_scheme_balancesheet_subtract_from} - ${( $tt->account_scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' )};
            if ( $amount!=0 ) {
                $arr[] = array(
                    'AccountNumber' => $tt->account_AccountNumber,
                    'AccountNumberURL' => urlencode( $tt->account_AccountNumber ),
                    'Title' => 'AccountNumber',
                    'amountDr' => $Dr,
                    'amountCr' => $Cr,
                    'amount' => $amount,
                    'side' => ( $amount > 0 ? $tt->account_scheme_balancesheet_subtract_from : ( $tt->account_scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' ) ),
                    'Head' => $tt->account_scheme_balancesheet_Head,
                    'SubtractFrom' => $tt->account_scheme_balancesheet_subtract_from
                );
            }
        }

        foreach ( $a as $aa ) {
            if ( array_search( $aa->AccountNumber, $account_found_in_tr ) === false ) {
                $Dr=$aa->OpeningBalanceDr;
                $Cr=$aa->OpeningBalanceCr;
                $amount= ${$aa->scheme_balancesheet_subtract_from} - ${( $aa->scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' )};
                if ( $amount !=0 ) {
                    $arr[] = array(
                        'AccountNumber' => $aa->AccountNumber,
                        'AccountNumberURL' => urlencode( $aa->AccountNumber ),
                        'Title' => 'AccountNumber',
                        'amountDr' => $Dr,
                        'amountCr' => $Cr,
                        'amount' => $amount,
                        'side' => ( $amount > 0 ? $aa->scheme_balancesheet_subtract_from : ( $aa->scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' ) ),
                        'Head' => $aa->scheme_balancesheet_Head,
                        'SubtractFrom' => $aa->scheme_balancesheet_subtract_from
                    );
                }

            }
        }

        $data['report'] = getReporttable( arrayToObject( $arr ),             //model
            array( "Account Number", 'amount', 'side' ),       //heads
            array( 'AccountNumber', 'amount', 'side' ),       //fields
            array( 'amount' ),        //totals_array
            array(),        //headers
            array( 'sno'=>true ),     //options
            "header",     //headerTemplate
            '',      //tableFooterTemplate
            "",      //footerTemplate,
            array( 'AccountNumber'=>array(
                    'task'=>'balancesheet_cont.digin',
                    'class'=>'alertinwindow',
                    'title'=>'_blank',
                    'url_post'=>array( 'digtype'=>'"AccountNumber"', 'format'=>'"raw"', 'digid'=>'#AccountNumberURL', 'pandl'=>"'$is_pandl'" )
                )
            )//Links array('field'=>array('task'=>,'class'=>''))
        );

        JRequest::setVar( "layout", "generalreport" );
        $this->load->view( 'report.html', $data );
        $this->jq->getHeader();


    }

    function diginPAndLGroup( $branch=null ) {
        $fromDate=$this->session->userdata( 'fromdate' );
        $toDate=$this->session->userdata( 'todate' );

        $is_pandl=false;
        if ( inp( 'pandl' ) ) {
            $month = date( 'm', strtotime( $fromDate ) );
            $year=date( 'Y', strtotime( $fromDate ) );
            if ( $month>=1 and $month <=3 ) {
                $year--;
            }
            $financialYearStart = "$year-04-01";
            // echo $financialYearStart . "<br/>";
            $is_pandl=true;
        }


        $dateOn = date( "Y-m-d", strtotime( date( "Y-m-d", strtotime( $toDate ) ) . " +1 DAY" ) );
        $t=new Transaction();
        $t->select( "SUM(amountDr) as amountDr, SUM(amountCr) as amountCr" );
        $t->include_related( 'account/scheme/balancesheet', 'subtract_from' );
        $t->include_related( 'account', 'AccountNumber' );
        $t->include_related( 'account', 'PandLGroup' );
        if ( $is_pandl ) {
            $t->where( 'created_at >=', $financialYearStart );
        }
        $t->where( "created_at <", $dateOn );
        $t->where_related( "account", "PandLGroup like '%".str_replace( " ", "%", urldecode( inp( 'digid' ) ) ). "%'" );
        if ( $branch!=null )
            $t->where( "branch_id", $branch );
        $t->group_start();
        $t->where_related( "account", "ActiveStatus", "1" );
        $t->or_where_related( "account", "affectsBalanceSheet", "1" );
        $t->group_end();
        $t->group_by( 'account_AccountNumber' );
        $t->get();

        // echo $t->check_last_query();

        // Accounts without Transactions
        $arr=array();

        $a=new Account();
        $a->select( 'OpeningBalanceDr' );
        $a->select( 'OpeningBalanceCr' );
        $a->select( 'AccountNumber' );
        $a->include_related( 'scheme/balancesheet', 'Head' );
        $a->include_related( 'scheme/balancesheet', 'subtract_from' );
        if ( $branch!=null )
            $a->where( "branch_id", $branch );
        $a->group_start();
        $a->where( "ActiveStatus", "1" );
        $a->or_where( "affectsBalanceSheet", "1" );
        $a->group_end();
        $a->where_related( 'scheme', 'Name', urldecode( inp( 'digid' ) ) );
        // $a->group_by('scheme_Name');
        $a->get();

        $account_found_in_tr = array();
        foreach ( $t as $tt ) {
            $Dr=$tt->amountDr;
            $Cr=$tt->amountCr;
            foreach ( $a as $aa ) {
                if ( $aa->AccountNumber == $tt->account_AccountNumber ) {
                    $Dr= $tt->amountDr + $aa->OpeningBalanceDr;
                    $Cr= $tt->amountCr + $aa->OpeningBalanceCr;
                    $account_found_in_tr[] = $aa->AccountNumber;
                }
            }
            // $Dr = $tt->amountDr + $tt->account_OpeningBalanceDr;
            // $Cr = $tt->amountCr + $tt->account_OpeningBalanceCr;
            $amount= ${$tt->account_scheme_balancesheet_subtract_from} - ${( $tt->account_scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' )};
            if ( $amount!=0 ) {
                $arr[] = array(
                    'AccountNumber' => $tt->account_AccountNumber,
                    'AccountNumberURL' => urlencode( $tt->account_AccountNumber ),
                    'Title' => 'AccountNumber',
                    'amountDr' => $Dr,
                    'amountCr' => $Cr,
                    'amount' => $amount,
                    'side' => ( $amount > 0 ? $tt->account_scheme_balancesheet_subtract_from : ( $tt->account_scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' ) ),
                    'Head' => $tt->account_scheme_balancesheet_Head,
                    'SubtractFrom' => $tt->account_scheme_balancesheet_subtract_from
                );
            }
        }

        foreach ( $a as $aa ) {
            if ( array_search( $aa->AccountNumber, $account_found_in_tr ) === false ) {
                $Dr=$aa->OpeningBalanceDr;
                $Cr=$aa->OpeningBalanceCr;
                $amount= ${$aa->scheme_balancesheet_subtract_from} - ${( $aa->scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' )};
                if ( $amount !=0 ) {
                    $arr[] = array(
                        'AccountNumber' => $aa->AccountNumber,
                        'AccountNumberURL' => urlencode( $aa->AccountNumber ),
                        'Title' => 'AccountNumber',
                        'amountDr' => $Dr,
                        'amountCr' => $Cr,
                        'amount' => $amount,
                        'side' => ( $amount > 0 ? $aa->scheme_balancesheet_subtract_from : ( $aa->scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' ) ),
                        'Head' => $aa->scheme_balancesheet_Head,
                        'SubtractFrom' => $aa->scheme_balancesheet_subtract_from
                    );
                }

            }
        }

        $data['report'] = getReporttable( arrayToObject( $arr ),             //model
            array( "Account Number", 'amount', 'side' ),       //heads
            array( 'AccountNumber', 'amount', 'side' ),       //fields
            array( 'amount' ),        //totals_array
            array(),        //headers
            array( 'sno'=>true ),     //options
            "header",     //headerTemplate
            '',      //tableFooterTemplate
            "",      //footerTemplate,
            array( 'AccountNumber'=>array(
                    'task'=>'balancesheet_cont.digin',
                    'class'=>'alertinwindow',
                    'title'=>'_blank',
                    'url_post'=>array( 'digtype'=>'"AccountNumber"', 'format'=>'"raw"', 'digid'=>'#AccountNumberURL', 'pandl'=>"'$is_pandl'" )
                )
            )//Links array('field'=>array('task'=>,'class'=>''))
        );

        JRequest::setVar( "layout", "generalreport" );
        $this->load->view( 'report.html', $data );
        $this->jq->getHeader();


    }

    function diginSchemeGroup( $branch=null ) {
        $fromDate=$this->session->userdata( 'fromdate' );
        $toDate=$this->session->userdata( 'todate' );

        $is_pandl=false;
        if ( inp( 'pandl' ) ) {
            $month = date( 'm', strtotime( $fromDate ) );
            $year=date( 'Y', strtotime( $fromDate ) );
            if ( $month>=1 and $month <=3 ) {
                $year--;
            }
            $financialYearStart = "$year-04-01";
            // echo $financialYearStart . "<br/>";
            $is_pandl=true;
        }

        $dateOn = date( "Y-m-d", strtotime( date( "Y-m-d", strtotime( $this->session->userdata( 'todate' ) ) ) . " +1 DAY" ) );
        $t=new Transaction();
        $t->select( "SUM(amountDr) as amountDr, SUM(amountCr) as amountCr" );
        $t->include_related( 'account/scheme/balancesheet', 'subtract_from' );
        $t->include_related( 'account/scheme', 'Name' );
        if ( $is_pandl ) {
            $t->where( "created_at >=", $financialYearStart );
        }
        $t->where( "created_at <", $dateOn );
        $t->where_related( "account/scheme", "SchemeGroup", urldecode( inp( 'digid' ) ) );
        if ( $branch!=null )
            $t->where( "branch_id", $branch );
        $t->group_start();
        $t->where_related( "account", "ActiveStatus", "1" );
        $t->or_where_related( "account", "affectsBalanceSheet", "1" );
        $t->group_end();
        $t->group_by( 'account_scheme_Name' );
        $t->get();

        $a=new Account();
        $a->select( 'SUM(OpeningBalanceDr) as OpeningBalanceDr' );
        $a->select( 'SUM(OpeningBalanceCr) as OpeningBalanceCr' );
        $a->include_related( 'scheme', 'Name' );
        $a->include_related( 'scheme/balancesheet', 'Head' );
        $a->include_related( 'scheme/balancesheet', 'subtract_from' );
        if ( $branch!=null )
            $a->where( "branch_id", $branch );
        $a->group_start();
        $a->where( "ActiveStatus", "1" );
        $a->or_where( "affectsBalanceSheet", "1" );
        $a->group_end();
        $a->where_related( 'scheme', 'SchemeGroup', urldecode( inp( 'digid' ) ) );
        $a->group_by( 'scheme_Name' );
        $a->get();
        // echo $a->check_last_query();

        $arr=array();

        $schemename_found_in_tr=array();
        foreach ( $t as $tt ) {
            foreach ( $a as $aa )
                if ( $aa->scheme_Name === $tt->account_scheme_Name ) {
                    $Dr=$tt->amountDr + $aa->OpeningBalanceDr;
                    $Cr=$tt->amountCr + $aa->OpeningBalanceCr;
                    $schemename_found_in_tr[] = $aa->scheme_Name;
                }
            $amount= ${$tt->account_scheme_balancesheet_subtract_from} - ${( $tt->account_scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' )};
            if ( $amount!=0 ) {
                $arr[] = array(
                    'SchemeName' => $tt->account_scheme_Name,
                    'SchemeNameURL' => urlencode( $tt->account_scheme_Name ),
                    'Title' => 'SchemeName',
                    'amountDr' => $Dr,
                    'amountCr' => $Cr,
                    'amount' => $amount,
                    'side' => ( $amount > 0 ? $tt->account_scheme_balancesheet_subtract_from : ( $tt->account_scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' ) ),
                    'Head' => $tt->account_scheme_balancesheet_Head,
                    'SubtractFrom' => $tt->account_scheme_balancesheet_subtract_from
                );
            }
        }

        foreach ( $a as $aa ) {
            if ( array_search( $aa->scheme_Name, $schemename_found_in_tr )===false ) {
                $Dr=$aa->OpeningBalanceDr;
                $Cr=$aa->OpeningBalanceCr;
                $amount= ${$aa->scheme_balancesheet_subtract_from} - ${( $aa->scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' )};
                if ( $amount !=0 ) {
                    $arr[] = array(
                        'SchemeName' => ( $aa->scheme_Name ),
                        'SchemeNameURL' => urlencode( $aa->scheme_Name ),
                        'Title' => 'SchemeName',
                        'amountDr' => $aa->OpeningBalanceDr,
                        'amountCr' => $aa->OpeningBalanceCr,
                        'amount' => $amount,
                        'side' => ( $amount > 0 ? $aa->scheme_balancesheet_subtract_from : ( $aa->scheme_balancesheet_subtract_from == 'Dr' ? 'Cr':'Dr' ) ),
                        'Head' => $aa->scheme_balancesheet_Head,
                        'SubtractFrom' => $aa->scheme_balancesheet_subtract_from
                    );
                }
            }
        }


        $data['report'] = getReporttable( arrayToObject( $arr ),             //model
            array( "Scheme Name", 'amount', 'side' ),       //heads
            array( 'SchemeName', 'amount', 'side' ),       //fields
            array( 'amount' ),        //totals_array
            array(),        //headers
            array( 'sno'=>true ),     //options
            "header",     //headerTemplate
            '',      //tableFooterTemplate
            "",      //footerTemplate,
            array( 'SchemeName'=>array(
                    'task'=>'balancesheet_cont.digin',
                    'class'=>'alertinwindow',
                    'title'=>'_blank',
                    'url_post'=>array( 'digtype'=>'"SchemeName"', 'format'=>'"raw"', 'digid'=>'#SchemeNameURL', 'pandl'=>"'$is_pandl'" )
                )
            )//Links array('field'=>array('task'=>,'class'=>''))
        );

        JRequest::setVar( "layout", "generalreport" );
        $this->load->view( 'report.html', $data );
        $this->jq->getHeader();
    }

    function diginAccountNumber() {
        $fromDate=$this->session->userdata( 'fromdate' );
        $toDate=$this->session->userdata( 'todate' );

        $is_pandl=false;
        if ( inp( 'pandl' ) ) {
            $month = date( 'm', strtotime( $fromDate ) );
            $year=date( 'Y', strtotime( $fromDate ) );
            if ( $month>=1 and $month <=3 ) {
                $year--;
            }
            $financialYearStart = "$year-04-01";
            // echo $financialYearStart . "<br/>";
            $is_pandl=true;
        }
        $dateOn = date( "Y-m-d", strtotime( date( "Y-m-d", strtotime( $this->session->userdata( 'todate' ) ) ) . " +1 DAY" ) );
        $t=new Transaction();
        $t->where_related( "account", 'AccountNumber', urldecode( inp( 'digid' ) ) );
        $t->where( 'created_at >=', $this->session->userdata( 'fromdate' ) );
        $t->where( 'created_at <', $dateOn );
        $t->get();

        $a = new Account();
        $a->where( 'AccountNumber', urldecode( inp( 'digid' ) ) );
        $a->get();
        // echo $a->check_last_query();
        $opb=$a->getOpeningBalance( $this->session->userdata( 'fromdate' ), null, 'both', $is_pandl );
        $clb=$a->getOpeningBalance( $dateOn, null, 'both', $is_pandl );
        $subtract_from = strtoupper( $a->scheme->balancesheet->subtract_from );

        $opb_val=$opb[$subtract_from] - $opb[( $subtract_from=='DR'?'CR':'DR' )];
        $opb_side = ( $opb_val<0? ( $subtract_from=='DR'?'CR':'DR' ) : $subtract_from );

        $clb_val=$clb[$subtract_from] - $clb[( $subtract_from=='DR'?'CR':'DR' )];
        $clb_side = ( $clb_val<0? ( $subtract_from=='DR'?'CR':'DR' ) : $subtract_from );
        if ( $is_pandl ) {
            $opb="--";
            $clb="--";
            $opb_val="--";
            $opb_side="--";
            $clb_val="--";
            $clb_side="--";
        }

        $data['report'] = getReporttable( $t,             //model
            array( 'Date', "Narration", 'amountDr', 'amountCr' ),       //heads
            array( '~date("d-M-Y",strtotime(#created_at))', 'Narration', 'amountDr', 'amountCr' ),       //fields
            array( 'amountDr', 'amountCr' ),        //totals_array
            array(),        //headers
            array( 'sno'=>true ),     //options
            "<h3 align='center'> Statement for Account $a->AccountNumber from $fromDate to $toDate ... </h3><h4 align='right'>Opening Balance : $opb_val $opb_side ... </h4><h4 align='right'>Closing Balance : $clb_val $clb_side</h4>",     //headerTemplate
            '',      //tableFooterTemplate
            "<h4 align='right'>Closing Balance : $clb_val $clb_side</h4>",      //footerTemplate,
            array( '~date("d-M-Y",strtotime(#created_at))'=>array(
                    'task'=>'report_cont.transactionDetails',
                    'class'=>'alertinwindow',
                    'title'=>'_blank',
                    'url_post'=>array( 'vn'=>'#voucher_no', 'format'=>'"raw"', 'tr_type'=>'#transaction_type_id', 'branch_id'=>'#branch_id' )
                )
            )//Links array('field'=>array('task'=>,'class'=>''))
        );

        JRequest::setVar( "layout", "generalreport" );
        $this->load->view( 'report.html', $data );
        $this->jq->getHeader();

    }


}
