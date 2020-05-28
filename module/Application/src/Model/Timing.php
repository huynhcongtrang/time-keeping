<?php

namespace Application\Model;

use Zend\Mvc\Plugin\Identity;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;
use Zend\Authentication\Storage\Session;
use Zend\Session\Storage\SessionStorage;
use Zend\View\Helper\Placeholder\Registry;
use Zend\Db\Sql\Sql; //note
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Apc;
use Zend\Cache\Storage\Plugin\ExceptionHandler;

class Timing{
    
    public function getAllDay($date_temp){
        
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = "CALL get_date_month(?,?,?)";
        $statement = $adapter->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute(array(
            'day' => $date_temp[2],
            'month' => $date_temp[1],
            'year' => $date_temp[0],
        ));
        return $db->fetch_array($result);
    }

    
    public function getDetailTiming($staff_id,$date_temp){
        $db = new \Database\Controller\AdapterController();
        $adapter = $db->DbAdapter();
        $sql = "CALL PR_my_check_in(?,?,?)";
        $statement = $adapter->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute(array(
            'staff_id' => $staff_id,
            'month' => $date_temp[1],
            'year' => $date_temp[0],
        ));
        return $db->fetch_array_timing($result);
    }
            
}
