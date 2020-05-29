<?php

/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session;
use Zend\Session\Storage\SessionStorage;
use Zend\Db\Sql\Sql;

class IndexController extends My_Controller {

    private $sql;

    public function __construct() 
    {
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $this->sql = new Sql($adapter);
    }

    public function indexAction() 
    {
        
        $auth = new AuthenticationService();
        if ($auth->hasIdentity() != 1) {
            header('Location: ' . HOST . 'user/login');
            exit();
        }
        
        $leaveTotalCurrentMonth = $this->getLeaveTotalCurrentMonth();
        $timeLateTotalCurrentMonth = $this->getTimeLateTotalCurrentMonth();
        $staffTempTotalCurrentMonth = $this->getStaffTempTotalCurrentMonth();
        // dump(json_encode($staffTempTotal));
        // exit();
        $staffTotal = $this->getStaffTotal()['staffTotal'];
        $teamTotal = $this->getTeamTotal()['teamTotal'];
        $officeTotal =$this->getOfficeTotal()['officeTotal'];
        $userTotal  =$this->getUserTotal()['userTotal'];
        $this->layout()->setVariable('title', 'Dashboard');
        return new ViewModel([
            'staffTotal' => $staffTotal,
            'leaveTotalCurrentMonth' => json_encode($leaveTotalCurrentMonth,JSON_UNESCAPED_SLASHES),
            'timeLateTotalCurrentMonth' => json_encode($timeLateTotalCurrentMonth,JSON_UNESCAPED_SLASHES),
            'staffTempTotalCurrentMonth' => json_encode($staffTempTotalCurrentMonth,JSON_UNESCAPED_SLASHES),
            'teamTotal' =>$teamTotal,
            'officeTotal' => $officeTotal,
            'userTotal' => $userTotal
        ]);
    }

    public function getStaffTotal()
    {    
        $select = $this->sql->select()
                ->from(array('s' => 'staff'))
                ->columns(array('staffTotal' => new \Zend\Db\Sql\Expression('COUNT(*)')));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->current();
    }
   

    public function getTeamTotal()
    {
        $select = $this->sql->select()
                ->from(array('t' => 'team'))
                ->columns(array('teamTotal' => new \Zend\Db\Sql\Expression('COUNT(*)')));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->current();
    }

    public function getOfficeTotal()
    {
        $select = $this->sql->select()
                ->from(array('o' => 'office'))
                ->columns(array('officeTotal' => new \Zend\Db\Sql\Expression('COUNT(*)')));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->current();
    }

    public function getUserTotal()
    {
        $select = $this->sql->select()
                ->from(array('u' => 'user'))
                ->columns(array('userTotal' => new \Zend\Db\Sql\Expression('COUNT(*)')));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result->current();
    }

    public function getLeaveTotalCurrentMonth()
    {
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();

        $query = sprintf("SELECT MONTH(`from_date`) AS month, COUNT(*) AS count FROM `leave_detail` WHERE YEAR(`from_date`) = YEAR(NOW()) GROUP BY MONTH(`from_date`) ORDER BY MONTH(`from_date`) ASC");
        
        $result = $adapter->query($query, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $result_arr =  $db->fetch_array($result);
        
        $i=0;
        foreach($result_arr as $item) {
           $leaveTotal[] = [
            'month' => $item['month'],
            'count' => $item['count']
           ];
            $i++;
            if($i == 5) break;
        }
        return $leaveTotal;
    }

    public function getTimeLateTotalCurrentMonth()
    {
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();

        $query = sprintf("SELECT MONTH(`date`) AS month, COUNT(*) AS count FROM `time_late` WHERE YEAR(`date`) = YEAR(NOW()) GROUP BY MONTH(`date`) ORDER BY MONTH(`date`) ASC");
        
        $result = $adapter->query($query, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $result_arr =  $db->fetch_array($result);
        
        $i=0;
        foreach($result_arr as $item) {
           $timeLateTotal[] = [
            'month' => $item['month'],
            'count' => $item['count']
           ];
            $i++;
            if($i == 5) break;
        }
        return $timeLateTotal;
    }

    public function getStaffTempTotalCurrentMonth()
    {
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();

        $query = sprintf("SELECT MONTH(`created_at`) AS month, COUNT(*) AS count FROM `staff_temp` WHERE YEAR(`created_at`) = YEAR(NOW()) GROUP BY MONTH(`created_at`) ORDER BY MONTH(`created_at`) ASC");
        
        $result = $adapter->query($query, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $result_arr =  $db->fetch_array($result);
        
        $i=0;
        foreach($result_arr as $item) {
           $staffTempTotal[] = [
            'month' => $item['month'],
            'count' => $item['count']
           ];
            $i++;
            if($i == 5) break;
        }
        return $staffTempTotal;
    }
}
