<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class databaseconvertor extends CI_Controller{

    function index() {
        echo "<form action='' method='get'>
Old prefix: <input type='text' name='old' /> <br />
New prefix: <input type='text' name='new' /> <br />
<input type='submit' value='Rename' />
</form>
";

        global $db_prefix;

        if ($old = $_REQUEST['old'] || $new = $_REQUEST['new']) {
            $result = db_query("SHOW TABLES");
            while ($r = db_fetch_array($result)) {
                $table_old = current($r);
                $table_new = $new . str_replace('^' . $old, '', $table_old);
                db_query("RENAME TABLE {$table_old} TO {$table_new}");
                print "{$table_old} &mdash;&gt; {$table_new} <br />";
            }

            $db_prefix = $new;
        }
    }


    function registerStaff(){
        $staff = new Staff();
        $staff->get();
        foreach($staff as $s){
            if($s->jid)
                continue;
            $s->jid = $s->saveJoomlaUser($s->StaffID, $s->Password, $s->details->Name, STAFF);
            $s->save();
        }
    }

    function renumberVouvchers(){
        $trans = $this->db->query("select voucher_no from jos_xtransactions where created_at >= (select yearly from jos_xclosings where branch_id = 2) and branch_id = 2 group by voucher_no order by created_at ASC")->result();
        $newvch = 0;
        $oldvch = 0;
        foreach($trans as $t){
//            $tr = new Transaction($t->id);
//            if($oldvch != $t->voucher_no)
                    $newvch = $newvch + 1;
//            $tr->dispaly_voucher_no = $newvch;
//            $tr->save();
//            $oldvch = $tr->voucher_no;
            $q = $this->db->query("update jos_xtransactions set display_voucher_no = $newvch where voucher_no = $t->voucher_no and branch_id = 2");
        }
        echo "done dona done done";
    }

}

?>
