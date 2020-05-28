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
        $all_date = $QTiming->getAllDay($date_temp);
        $day_advance = $QSettingDate->getDayAdvance($date_temp);
        $day_all_advance = array();//get day advance
        foreach ($day_advance as $key=>$item){
            $day_all_advance[] = $key;
        }
        
        $detailTiming = $QTiming->getDetailTiming($user->staff_id, $date_temp);
//        echo "<pre>";
//        print_r($detailTiming);
//        echo "</pre>";
//        exit();
        $this->layout()->setVariable('title', 'My Check In');
        return new ViewModel([
            'all_date' => $all_date ,
            'day_all_advance' => $day_all_advance , 
            'day_advance' => $day_advance,
            'detail_timing' => $detailTiming
        ]);
    }
}
