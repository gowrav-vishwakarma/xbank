<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class inventory_cont extends CI_Controller{

 	function inventory_cont(){
 		parent::__construct();
 	}


        function dashboard(){
            xDeveloperToolBars::getInventoryManagementToolBar();
            $this->load->view("inventory.html");
            $this->jq->getHeader();
        }


/**
 * Function to add a new category in the inventory list.
 * Does not actually creates a category.
 * Shows the form for category.
 * Sends the link to {@link newCategory}
 */
 	function newCategoryForm(){
            xDeveloperToolBars::onlyCancel("inventory_cont.dashboard", "cancel", "Add a new Category here");
            $this->load->library('form');
            $this->form->open("one",'index.php?option=com_xbank&task=inventory_cont.newCategory')
            ->setColumns(2)
            ->text("Category Name","name='CategoryName' class='input req-string'")
            ->textArea("Description","name='Description'")
//            ->confirmButton("Confirm","New Category to create","index.php?//mod_inventory/inventory_cont/confirmCategoryCreateForm",true)
            ->submit('Create');
            $data['contents']=$this->form->get();
            $data['result']=$this->db->query("select id, Name, Description from jos_xcategory")->result();
            if(JFactory::getUser()->gid >= 24)
                echo $this->form->get();
            JRequest::setVar("layout","categoryView");
            $this->load->view('inventory.html', $data);
            $this->jq->getHeader();
        }

/**
 *
 * @return <type>
 * Actually new category is created.
 * Takes the name and description of the category
 */
       function newCategory(){

            try{
                        $this->db->trans_begin();
	 		$categ=new Category();
	 		$categ->Name=inp('CategoryName');
	 		$categ->Description=inp('Description');
                        $categ->save();

                        $this->db->trans_commit();
                } catch (Exception $e) {
                    $rollback = true;
                }
                if ($this->db->trans_status() === false or $rollback == true) {
                    $this->db->trans_rollback();
                    re("inventory_cont.newCategoryForm", " Category Not Added ", "error");
                }
                $this->db->trans_commit();
                re('inventory_cont.newCategoryForm',"New Category Successfully Added");

        }

/**
 *
 * @param <type> $id
 * Function to Edit the category.
 * Does not actually edit the category. Generates a form
 * Sends the link to {@link editCategory}
 */
        function editCategoryForm($id=''){
//            Staff::accessibleTo(ADMIN);

//            setInfo("EDIT CATEGORY","");
            $id = JRequest::getVar("id");
            $category=new Category($id);

            $this->load->library('form');
            $this->form->open("one","index.php?option=com_xbank&task=inventory_cont.editCategory&id=$id")
            ->setColumns(2)
            ->text("Category Name","name='CategoryName' class='input req-string' value='$category->Name'")
            ->textArea("Description","name='Description' ","",$category->Description)
//            ->confirmButton("Confirm","New Category to create","index.php?//mod_inventory/inventory_cont/confirmCategoryCreateForm",true)
            ->submit('Edit')
            ->resetBtn("Reset");
            if(JFactory::getUser()->gid >= 24)
                echo $this->form->get();
            $data['result']=$this->db->query("select id, Name, Description from jos_xcategory")->result();
            JRequest::setVar("layout","categoryView");
            $this->load->view("inventory.html",$data);
            $this->jq->getHeader();
        }

/**
 *
 * @param <type> $id
 * @return <type>
 * Actually edit the category.
 */
        function editCategory($id=''){
                try{
                $id = JRequest::getVar("id");
                $this->db->trans_begin();
                $categ=new Category($id);
                $categ->Name=inp('CategoryName');
                $categ->Description=inp('Description');
                $categ->save();

                $this->db->trans_commit();
                } catch (Exception $e) {
                    $rollback = true;
                }
                if ($this->db->trans_status() === false or $rollback == true) {
                    $this->db->trans_rollback();
                    re("inventory_cont.newCategoryForm", " Category Not Edited ", "error");
                }
                $this->db->trans_commit();
                re('inventory_cont.newCategoryForm',"Category ".inp('CategoryName')." Successfully Edited");

        }

/**
 * Function deletes a category based on parameter {@param id}
 */
        function removeCategory($id=''){
//            Staff::accessibleTo(ADMIN);

             try{
			//TODO- Account number addition + Datatable all fields recheck
			//User ID Unique
                        $id = JRequest::getVar("id");
                        $this->db->trans_begin();
                        $categ = new Category($id);
                        $categ_name = $categ->Name;
			$q="delete from jos_xcategory where id = $id";
                         executeQuery($q);
                        $this->db->trans_commit();
                } catch (Exception $e) {
                    $rollback = true;
                }
                if ($this->db->trans_status() === false or $rollback == true) {
                    $this->db->trans_rollback();
                    re("inventory_cont.newCategoryForm", " Category Not Removed ", "error");
                }
                $this->db->trans_commit();
                re('inventory_cont.newCategoryForm',"Category $categ_name Successfully Removed");
        }

/**
 * Function to add a new item in the inventory list.
 * Does not actually creates an item.
 * Shows the form for item.
 * Sends the link to {@link newItem}
 */
        function newItemForm(){
//            Staff::accessibleTo(ADMIN);

//            setInfo("ADD NEW ITEM","");
            xDeveloperToolBars::onlyCancel("inventory_cont.dashboard", "cancel", "Add a new Item here");
            $this->load->library('form');
            $this->form->open("one",'index.php?option=com_xbank&task=inventory_cont.newItem')
            ->setColumns(2)
            ->text("Item Name","name='ItemName' class='input req-string'")
//            ->text("Category Name","name='CategoryName' class='input req-string'")
            ->lookupDB("Under Category", "name='CategoryID' class='input'", "index.php?option=com_xbank&task=inventory_cont.getCategory&format=raw", array("a"=>"b"), array("id","Name"), "id")
            ->text("Item Quantity","name='Quantity' class='input'")
            ->textArea("Description","name='Description'")
//            ->confirmButton("Confirm","New Category to create","index.php?//mod_inventory/inventory_cont/confirmInventoryCreateForm",true)
            ->_()
            ->submit('Create');
            if(JFactory::getUser()->gid >= 24)
                echo $this->form->get();
           // TODO
//           $data['result']=$this->db->query("select sl.id, i.Name, i.Description, c.Name as CName, sl.QuantityAlloted  as Qty, sl.StockAllotedDate as DateBought from items i join category c on i.category_id=c.id join stock_log sl on i.id=sl.items_id where sl.StockStatus = 1")->result();
           $data['result']=$this->db->query("select i.*, c.Name as CName from jos_xitems i join jos_xcategory c on i.category_id=c.id")->result();
           JRequest::setVar("layout","itemView");
           $this->load->view("inventory.html",$data);
           $this->jq->getHeader();
        }



         function getCategory(){
            $list = array();
            $q = "select id, Name
                    from jos_xcategory
                    where Name Like '%".$this->input->post("term")."%'
                    or id like '%".$this->input->post("term")."%'";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array("id"=>$dd->id, "Name" => $dd->Name);
        }
        echo '{"tags":' . json_encode($list) . '}';
        }

/**
 *
 * @return <type>
 * Actually new item is created.
 *
 * STEPS
 * Create a new item
 * If the quantity of the item is greater than zero, Create a new stock and also maintain the stock log
 *
 */
        function newItem(){
//            Staff::accessibleTo(ADMIN);

            try{
			$this->db->trans_begin();
	 		$item=new Item();
	 		$item->Name=inp('ItemName');
                        $item->category_id=inp('CategoryID');
	 		$item->Description=inp('Description');
                        $item->save();

                        if(inp('Quantity')!=""){
                            $stock = new Stock();
                            $stock->Quantity=inp('Quantity');
                            $stock->items_id=$item->id;
                            $stock->save();

                            $sl = new StockLog();
                            $sl->StockAllotedDate=getNow("Y-m-d");
                            $sl->branch_id =  Branch::getCurrentBranch()->id;
                            $sl->QuantityAlloted=inp('Quantity');
                            $sl->items_id=$item->id;
                            $sl->StockStatus = STOCK_ADDED;
                            $sl->save();
                        }

                        $this->db->trans_commit();
                } catch (Exception $e) {
                    $rollback = true;
                }
                if ($this->db->trans_status() === false or $rollback == true) {
                    $this->db->trans_rollback();
                    re("inventory_cont.newItemForm", " Item Not Added ", "error");
                }
                $this->db->trans_commit();
                re('inventory_cont.newItemForm',"New Item Successfully Added");

        }

/**
 *
 * @param <type> $id
 * Does not actually edits the item.
 * Creates a form.
 * Sends the link to {@link editItem}
 */
        function editItemForm($id=''){
            $id = JRequest::getVar("id");
            $items=new Item($id);

            $this->load->library('form');
            $form = $this->form->open("one","index.php?option=com_xbank&task=inventory_cont.editItem&id=$id")
            ->setColumns(2)
            ->text("Item Name","name='ItemName' class='input req-string' value='$items->Name' ")
//            ->text("Category Name","name='CategoryName' class='input req-string'")
           
            ->lookupDB("Under Category", "name='CategoryID' value='$items->category_id' class='input'", "index.php?option=com_xbank&task=inventory_cont.getCategory&format=raw", array("a"=>"b"), array("id","Name"), "id");
            $stock=new StockLog();
            $stock->where("items_id",$id)->get();
            $form = $form->text("Item Quantity bought","name='Quantity' class='input req-numeric' value='$stock->QuantityAlloted'")
//           ->text("New Quantity bought","name='NewQuantity' class='input req-numeric' value='0'")
           ->textArea("Description","name='Description'","",$items->Description)
//            ->confirmButton("Confirm","New Category to create","index.php?//mod_inventory/inventory_cont/confirmInventoryCreateForm",true)
           ->_()
           
           ->submit('Edit')
           ->resetBtn("Reset");

            if(JFactory::getUser()->gid >= 24)
                echo $this->form->get();
//           $data['result']=$this->db->query("select sl.id, i.Name, i.Description, c.Name as CName, sl.QuantityAlloted  as Qty, sl.StockAllotedDate as DateBought from items i join category c on i.category_id=c.id join stock_log sl on i.id=sl.items_id where sl.StockStatus = 1")->result();
           $data['result']=$this->db->query("select i.*, c.Name as CName from jos_xitems i join jos_xcategory c on i.category_id=c.id")->result();
           JRequest::setVar("layout","itemView");
           $this->load->view("inventory.html",$data);
           $this->jq->getHeader();
        }

/**
 * Actually edit the item
 */
        function editItem($id=''){
//            Staff::accessibleTo(ADMIN);

            try{

			//TODO- Account number addition + Datatable all fields recheck
			//User ID Unique
                        $id = JRequest::getVar("id");
			$this->db->trans_begin();
	 		$item=new Item($id);

	 		$item->Name=inp('ItemName');
                        $item->category_id=inp('CategoryID');
	 		$item->Description=inp('Description');
                        $item->save();

                        $stock=new StockLog();
                        $stock->where("items_id",$id)->get();
                        $stock->QuantityAlloted=inp('Quantity');
                        $stock->save();
                        $this->db->trans_commit();
                } catch (Exception $e) {
                    $rollback = true;
                }
                if ($this->db->trans_status() === false or $rollback == true) {
                    $this->db->trans_rollback();
                    re("inventory_cont.newItemForm", " Item Not Edited ", "error");
                }
                $this->db->trans_commit();
                re('inventory_cont.newItemForm',"Item Successfully Edited");
        }

/**
 *
 * @param <type> $id
 * @return <type>
 * Delete the item from the inventory list
 */
        function removeItem($id){
//            Staff::accessibleTo(ADMIN);

             try{
			//TODO- Account number addition + Datatable all fields recheck
			//User ID Unique
			$id = JRequest::getVar("id");
                        $item = new Item($id);
                        $item_name = $item->Name;
                        $this->db->trans_begin();
//	 		$categ=Doctrine::getTable('Category')->find($id);
	 		$q="delete from jos_xitems where id = $id";
                         executeQuery($q);
                       $this->db->trans_commit();
                } catch (Exception $e) {
                    $rollback = true;
                }
                if ($this->db->trans_status() === false or $rollback == true) {
                    $this->db->trans_rollback();
                    re("inventory_cont.newItemForm", " Item Not Removed ", "error");
                }
                $this->db->trans_commit();
                re('inventory_cont.newItemForm',"Item $item_name Successfully Removed");
        }

        

/**
 * Does not actually add new stock to the inventory
 * Generates the <b>FORM</b> for adding the stock
 * Sends the link to {@link addNewStock}
 */
        function addNewStockForm(){
//            Staff::accessibleTo(ADMIN);

//            setInfo("ADD/REMOVE STOCK","");
            xDeveloperToolBars::onlyCancel("inventory_cont.dashboard", "cancel", "Add/Remove Stock here");
            $this->load->library('form');
            $this->form->open("one",'index.php?option=com_xbank&task=inventory_cont.addNewStock')
            ->setColumns(2)
            
            ->lookupDB("Item Name", "name='ItemID' class='input'", "index.php?option=com_xbank&task=inventory_cont.getItem&format=raw", array("a"=>"b"), array("id","Name","cName"), "id")
            ->select("Add/Remove","name='StockStatus'",array("Add"=>'1',"Remove"=>'0'))
            ->text("Item Quantity","name='Quantity' class='input req-numeric'")
//            ->textArea("Description","name='Description'")
//            ->confirmButton("Confirm","New Category to create","index.php?//mod_inventory/inventory_cont/confirmInventoryCreateForm",true)
            ->_()
            ->submit('Create');
            if(JFactory::getUser()->gid >= 24)
                echo $this->form->get();
           $data['result']=$this->db->query("select sl.id as id, i.Name as itemName, sl.StockAllotedDate as StockAllotedDate, sl.QuantityAlloted as Quantity, sl.StockStatus as StockStatus from jos_xstock_log sl inner join jos_xitems i on i.id=sl.items_id where sl.StockStatus=".STOCK_ADDED." or sl.StockStatus=".STOCK_REMOVED)->result();
//           $data['result']=$this->db->query("select i.id, i.Name, i.Description, c.Name as CName, (s.Quantity - s.StockAlloted) as Qty from items i join category c on i.category_id=c.id join stock s on i.id=s.items_id")->result();
           JRequest::setVar("layout","addRemoveStockView");
           $this->load->view("inventory.html",$data);
           $this->jq->getHeader();
        }


        function getItem(){
            $list = array();
            $q = "select i.id, i.Name as iName, c.Name as cName
                    from jos_xitems i join jos_xcategory c on c.id=i.category_id
                    where i.Name Like '%".$this->input->post("term")."%'
                    or i.id like '%".$this->input->post("term")."%'";
        $result = $this->db->query($q)->result();
        foreach ($result as $dd) {
            $list[] = array("id"=>$dd->id, "Name" => $dd->iName, "cName" => $dd->cName);
        }
        echo '{"tags":' . json_encode($list) . '}';
        }


/**
 * Whenever an existing item's stock is added or removed or destroyed, it is maintained by stock_log table
 * Function actually add or remove the stock
 */
         function addNewStock(){
//            Staff::accessibleTo(ADMIN);

            try{
			$this->db->trans_begin();
                        $stockLog = new Stocklog();

                        $stockLog->StockAllotedDate = getNow("Y-m-d");
//                        $stockLog->branch_id=inp("Branch");
                        $stockLog->QuantityAlloted=inp("Quantity");
                        $stockLog->items_id=inp("ItemID");
                        $stockLog->StockStatus=inp("StockStatus");
                        $stockLog->save();

//                        $stock=Doctrine::getTable('Stock')->findOneByItems_id(inp("ItemID"));
//                        $stock->StockAlloted=$stock->StockAlloted + inp('StockAlloted');
//                        $stock->save();

                        $this->db->trans_commit();
                } catch (Exception $e) {
                    $rollback = true;
                }
                if ($this->db->trans_status() === false or $rollback == true) {
                    $this->db->trans_rollback();
                    re("inventory_cont.addNewStockForm", " Stock Not Added", "error");
                }
                $this->db->trans_commit();
                re('inventory_cont.addNewStockForm',"Stock Successfully Added");

        }


/**
 *
 * @param <type> $id
 * Does not actually edit the stock
 * Generates the <b>FORM</b> for editing the stock
 */
            function editNewStockForm($id=''){
//                Staff::accessibleTo(ADMIN);

//                setInfo("EDIT STOCK","");
//                $stocklog=Doctrine::getTable('StockLog')->find($id);
                xDeveloperToolBars::onlyCancel("inventory_cont.dashboard", "cancel", "Edit Stock here");
                $id = JRequest::getVar("id");
                $stocklog = new Stocklog($id);
                $this->load->library('form');
                $this->form->open("one","index.php?option=com_xbank&task=inventory_cont.editNewStock&id=$id")
                ->setColumns(2)
                ->text("Item Name","name='ItemID' class='input' value='$stocklog->items_id' READONLY")
                ->select("Add/Remove","name='StockStatus'",array("Add"=>'1',"Remove"=>'0'),$stocklog->StockStatus)
                ->text("Item Quantity","name='Quantity' class='input req-numeric' value='$stocklog->QuantityAlloted'")
    //            ->textArea("Description","name='Description'")
    //            ->confirmButton("Confirm","New Category to create","index.php?//mod_inventory/inventory_cont/confirmInventoryCreateForm",true)
                ->datebox("Date of Stock Adding/Removal","name='stockdate' value='$stocklog->StockAllotedDate'")
                ->_()
                ->submit('Edit')
                ->resetBtn("Reset");

                if(JFactory::getUser()->gid >= 24)
                    echo $this->form->get();
               $data['result']=$this->db->query("select i.id as id, i.Name as itemName, sl.StockAllotedDate as StockAllotedDate, sl.QuantityAlloted as Quantity, sl.StockStatus as StockStatus from jos_xstock_log sl inner join jos_xitems i on i.id=sl.items_id where sl.StockStatus=".STOCK_ADDED." or sl.StockStatus=".STOCK_REMOVED)->result();
    //           $data['result']=$this->db->query("select i.id, i.Name, i.Description, c.Name as CName, (s.Quantity - s.StockAlloted) as Qty from items i join category c on i.category_id=c.id join stock s on i.id=s.items_id")->result();
               JRequest::setVar("layout","addRemoveStockView");
               $this->load->view("inventory.html",$data);
               $this->jq->getHeader();
        }

/**
 *
 * @param <type> $id
 * @return <type>
 * Function actually edit the stock
 */
         function editNewStock($id){
//            Staff::accessibleTo(ADMIN);

            try{
//			$conn = Doctrine_Manager::connection();
//			$conn->beginTransaction();
                        JRequest::getVar("id");
                        $this->db->trans_begin();
                        $stockLog = new Stocklog($id);

                        $stockLog->StockAllotedDate = inp("stockdate");
//                        $stockLog->branch_id=inp("Branch");
                        $stockLog->QuantityAlloted=inp("Quantity");
//                        $stockLog->items_id=inp("ItemID");
                        $stockLog->StockStatus=inp("StockStatus");
                        $stockLog->save();

//                        $stock=Doctrine::getTable('Stock')->findOneByItems_id(inp("ItemID"));
//                        $stock->StockAlloted=$stock->StockAlloted + inp('StockAlloted');
//                        $stock->save();

                        $this->db->trans_commit();
                } catch (Exception $e) {
                    $rollback = true;
                }
                if ($this->db->trans_status() === false or $rollback == true) {
                    $this->db->trans_rollback();
                    re("inventory_cont.addNewStockForm", " Stock Not Edited", "error");
                }
                $this->db->trans_commit();
                re('inventory_cont.addNewStockForm',"Stock Successfully Updated");

        }


/**
 * Actual management of stock is not done
 * Generates a <b>FORM</b> for stock management
 * Sends the link to {@link manageStock} for managing stock
 */
        function manageStockForm(){
//            Staff::accessibleTo(ADMIN);

//             setInfo("MANAGE STOCK","");
            xDeveloperToolBars::onlyCancel("inventory_cont.dashboard", "cancel", "Manage Stock here");
            $this->load->library('form');
            $form=$this->form->open("one",'index.php?option=com_xbank&task=inventory_cont.manageStock')
            ->setColumns(2)
//            ->text("Item Name","name='Item' class='input req-string'")
            ->lookupDB("Item Name","name='ItemID' class='input req-string ui-autocomplete-input'","index.php?option=com_xbank&task=inventory_cont.getStock&format=raw",
 			array("a" => "b"),
			array("Id","Name","StockInHand","CategName"),"Id")
           ->lookupDB("Branch","name='Branch' class='input req-string ui-autocomplete-input'","index.php?option=com_xbank&task=inventory_cont.getBranch&format=raw",
 			array("a" => "b"),
			array("Id","Name"),"Id")
           ->text("Quantity of Stock To be Alloted/Returned","name='StockAlloted' class='input req-numeric req-min' minlength ='1'")
           ->checkBox("Stock Returned","name='StockReturned' class='input' value='1'")
           ->confirmButton("Confirm","Manage Stock","index.php?option=com_xbank&task=inventory_cont.confirmManageStockForm&format=raw",true)
           ->submit('Create');

            //TODO
            $data['result']=$this->db->query("select i.Name as itemName, sl.StockAllotedDate as StockAllotedDate, sl.QuantityAlloted as QuantityAlloted,sl.StockStatus as StockStatus, b.Name as BranchName from jos_xstock_log sl inner join jos_xitems i on i.id=sl.items_id left join jos_xbranch b on sl.branch_id=b.id  where ( sl.StockStatus=".STOCK_ALLOTED." OR sl.StockStatus=".STOCK_RETURNED." )  order by sl.StockAllotedDate desc")->result();

            if(JFactory::getUser()->gid >= 24)
                echo $this->form->get();
           JRequest::setVar("layout","stockAllotedView");
           $this->load->view("inventory.html",$data);
           $this->jq->getHeader();

        }


        function getStock(){
            $list = array();
            $q = "select i.id as Id, i.Name as Name,( if((select SUM(QuantityAlloted) from jos_xstock_log where items_id = i.id and StockStatus=".STOCK_ADDED.") is not null,(select SUM(QuantityAlloted) from jos_xstock_log where items_id = i.id and StockStatus=".STOCK_ADDED."),0 ) + if((select SUM(QuantityAlloted) from jos_xstock_log where items_id = i.id and StockStatus=".STOCK_RETURNED.") is not null,(select SUM(QuantityAlloted) from jos_xstock_log where items_id = i.id and StockStatus=".STOCK_RETURNED."),0 ) - if((select SUM(QuantityAlloted) from jos_xstock_log where items_id = i.id and StockStatus=".STOCK_REMOVED.") is not null,(select SUM(QuantityAlloted) from jos_xstock_log where items_id = i.id and StockStatus=".STOCK_REMOVED."),0 )- if((select SUM(QuantityAlloted) from jos_xstock_log where items_id = i.id and StockStatus=".STOCK_ALLOTED.") is not null,(select SUM(QuantityAlloted) from jos_xstock_log where items_id = i.id and StockStatus=".STOCK_ALLOTED."),0 )) as StockInHand ,c.Name as CategName
				from jos_xitems i left join jos_xcategory c on c.id=i.category_id inner join jos_xstock_log s on s.items_id=i.id
                                where i.Name like '%".$this->input->post("term")."%' or i.id like '%".$this->input->post("term")."%' group by s.items_id";

            $result = $this->db->query($q)->result();
            foreach ($result as $dd) {
                $list[] = array("Id" => $dd->Id ,"Name"=>$dd->Name, "StockInHand" => $dd->StockInHand,"CategName" => $dd->CategName);
            }
            echo '{"tags":' . json_encode($list) . '}';

        }

        function getBranch(){
            $list = array();
            $q = "select b.id as Id, b.Name as Name from jos_xbranch b where b.Name like '%".$this->input->post("term")."%' or b.id like '%".$this->input->post("term")."%'";

            $result = $this->db->query($q)->result();
            foreach ($result as $dd) {
                $list[] = array("Id" => $dd->Id ,"Name"=>$dd->Name);
            }
            echo '{"tags":' . json_encode($list) . '}';

        }

/**
 * Function confirms the allocation of inventory items to a branch
 * Checks that the number of alloted items does not exceed the stock present
 */
        function confirmManageStockForm(){
//            Staff::accessibleTo(ADMIN);

            $msg="";
            $err=false;

            $stock=new Stock();
            $stock->where("items_id",inp("ItemID"))->get();
            $stockAdded=$this->db->query("Select SUM(QuantityAlloted) as StockAdded from jos_xstock_log where items_id=".inp("ItemID")." and StockStatus = ".STOCK_ADDED)->row()->StockAdded;
            $stockRemoved=$this->db->query("Select SUM(QuantityAlloted) as StockRemoved from jos_xstock_log where items_id=".inp("ItemID")." and StockStatus = ".STOCK_REMOVED)->row()->StockRemoved;
            $stockAlloted=$this->db->query("Select SUM(QuantityAlloted) as StockAlloted from jos_xstock_log where items_id=".inp("ItemID")." and StockStatus = ".STOCK_ALLOTED)->row()->StockAlloted;
            $stockReturned=$this->db->query("Select SUM(QuantityAlloted) as StockReturned from jos_xstock_log where items_id=".inp("ItemID")." and StockStatus = ".STOCK_RETURNED)->row()->StockReturned;

            $branch=new Branch(inp("Branch"));
            if($stock AND $branch AND inp('StockAlloted')!=""){
//                $stockInHand = $stock->Quantity - $stock->StockAlloted;
                $stockInHand=$stockAdded - ($stockAlloted - $stockReturned + $stockRemoved);
                if($stockInHand < inp("StockAlloted") && !inp("StockReturned")){
                    $err=true;
                    $msg .="<h2>Available Stock = ".$stockInHand."<br/>You are requesting to allot ".inp("StockAlloted")." items which are more than the available Stock...</h2><br/>falsefalse";
                }
                else{
                    if(inp("StockReturned")){
                        $msg .="<h2>Available Stock = ".$stockInHand."<br/>You are returning ".inp("StockAlloted")." items.</h2><br/><h3>Proceed with Stock Return....</h3><br/>";
                    }
                    else
                        $msg .="<h2>Available Stock = ".$stockInHand."<br/>You requested ".inp("StockAlloted")." items.</h2><br/><h3>Proceed with Stock Allocation....</h3><br/>";
                    }
              }
              else{
                  $msg .="falsefalse";
              }
            echo $msg;
        }

/**
 *
 * @return <type>
 * Actual management of stock is done
 * New Stock Log entry is done
 */
        function manageStock(){
//            Staff::accessibleTo(ADMIN);

            try{
			$this->db->trans_begin();
                        $stockLog = new Stocklog();

                        $stockLog->StockAllotedDate = getNow();
                        $stockLog->branch_id=inp("Branch");
                        $stockLog->QuantityAlloted=inp("StockAlloted");
                        $stockLog->items_id=inp("ItemID");
                        $stockLog->StockStatus=(inp("StockReturned") ? STOCK_RETURNED : STOCK_ALLOTED);//STOCK_ALLOTED;
                        $stockLog->save();

//                        $stock=Doctrine::getTable('Stock')->findOneByItems_id(inp("ItemID"));
//                        $stock->StockAlloted=$stock->StockAlloted + inp('StockAlloted');
//                        $stock->save();

                        $this->db->trans_commit();
                } catch (Exception $e) {
                    $rollback = true;
                }
                if ($this->db->trans_status() === false or $rollback == true) {
                    $this->db->trans_rollback();
                    re("inventory_cont.manageStockForm", " Stock Not Edited", "error");
                }
                $this->db->trans_commit();
                re('inventory_cont.manageStockForm',"Stock Successfully Alloted");
        }


}
?>
