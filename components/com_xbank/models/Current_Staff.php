<?php
/**
 * Current_Staff : Performs login logout and current staff related functions
 * 
 * This calss has static member staff which stores the current staff <b>and saves its id insession also</b> as 'staff_id'
 * this is singleton class meanse you cannot create a duplicate Current_Staff class my any meanse , by doing so you only will point to already created class
 * 
 * @package    xBank
 * @subpackage xModels
 * @author     Gowrav  <GowravVishwakarma@gmail.com>
 * @version    1.0.0
 * @category Timestampable
 */
 class Current_Staff{
 	static $staff;
 	private function __constructor(){}
/**
 * get the current Staff::DoctrineRecord object 
 * @uses staff Current_Staff::staff()
 */
 	public static function staff(){
 		if(!isset(self::$staff)){
 			
 			if(! $staff_id=JFactory::getUser()->id){
 				return FALSE;
 			}
 			$s = new Staff();
                        $s->get_by_jid(JFactory::getUser()->id);
 			if(! $s){
 				return FALSE;
 			}
 			self::$staff = $s;
 		}
 		return self::$staff;
 	}
 
 /**
  * performs login action for any satff
  * 
  * On sucessfull login it also saves the Staff::DoctrineRecord in static $staff variable and staff_id in session
  * @param integer branch_id not the Branch::DoctrineRecord Object
  * @param integer staff_id not the Staff::DoctrineRecord Object
  * @param string Password simple password
  * @return boolean login succeeded or not
  * 
  * <b>NOTE : LOGOUT IS SIMPLY DONE BY {@link auth Auth Module by removing staff_id from session}</b>
  */
 	public static function login($branch, $StaffID, $password){
            $s=new Staff();
            $s->where("StaffID",$StaffID)->get();
 		if($s){
 			$s_input = new Staff();
 			$s_input->Password = $password;
 			if($s_input->Password == $s->Password && $s->branch_id == $branch){
 				unset($s_input);
 				$CI=& get_instance();
// 				$CI->load->library('session');
 				$CI->XAVOC_session->set_userdata('staff_id',$s->id);
 				self::$staff=$s;
 				return TRUE;
 			}
 			unset($s_input);
 		}
 		return FALSE;
 	}
 	
 	public function __clone(){
 		trigger_error('Clone is Not Allowed',E_USER_ERROR);
 	}
 	
 }
?>
