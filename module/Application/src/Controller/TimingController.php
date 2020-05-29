<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TimingController extends My_Controller
{
    public function indexAction()
    {
        $QTiming = new \Application\Model\Timing();
        $QSettingDate = new \Application\Model\SettingDay();
        if ($user = $this->identity()) {
            
        } else {
            header('Location: ' . HOST . 'user/login');
            exit();
        }
        
        $date_temp = explode('-', date("Y-m-t"));
        if ($this->getRequest()->isPost()) {
            $date = $this->params()->fromPost('date');
            $date_temp1 = explode("-", $date);
            $date_convert = $date_temp1[1].'-'.$date_temp1[0].'-'.date("t");
            $date_temp = explode("-", $date_convert);
        }
        $all_date = $QTiming->getAllDay($date_temp);
        $day_advance = $QSettingDate->getDayAdvance($date_temp);
        $day_all_advance = array();//get day advance
        foreach ($day_advance as $key=>$item){
            $day_all_advance[] = $key;
        }
        
        $detailTiming = $QTiming->getDetailTiming($user->staff_id, $date_temp);
        $shift_stafff =  $QTiming->getShiftById($user->staff_id);
        
        $this->layout()->setVariable('title', 'My Check In');
        return new ViewModel([
            'all_date' => $all_date ,
            'day_all_advance' => $day_all_advance , 
            'day_advance' => $day_advance,
            'detail_timing' => $detailTiming,
            'shift_stafff' => $shift_stafff,
            'date_temp' => $date_temp
        ]);
    }
    
    
    public function getReasonTempAction(){
        $QTiming = new \Application\Model\Timing();
        if ($this->getRequest()->isPost()) {
            $id = $this->params()->fromPost('id',0);
            if($id != 0) {
                echo json_encode($QTiming->getReasonTemp($id));
                exit();
            }
            echo json_encode($QTiming->getReasonTemp());
            exit();
        }
    }
    
    public function getReasonLateAction(){
        $QTiming = new \Application\Model\Timing();
        if ($this->getRequest()->isPost()) {
            echo json_encode($QTiming->getReasonLate());
            exit();
        }
    }
}
