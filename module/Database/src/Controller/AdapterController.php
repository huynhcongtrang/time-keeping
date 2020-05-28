<?php

namespace Database\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql; //note

class AdapterController extends AbstractActionController {

    public function DbAdapter() {
        $adapter = new Adapter(array(
            'driver' => 'Mysqli',
            'database' => 'time_keeping',
            'username' => 'root',
            'password' => '',
            'hostname' => 'localhost',
            'charset' => 'utf8'
        ));
        return $adapter;
    }

    public function fetch_array($data) {
        $data_return = array();
        foreach ($data as $key => $item) {
            $data_return[$key] = $item;
        }
        return $data_return;
    }
    
    public function fetch_array_key_value($key,$value,$data){
        $data_return = array();
        foreach ($data as $item){
            $data_return[$item[$key]] = $item[$value];
        }
        return $data_return;
    }
    
    public function fetch_array_timing($data){
        $array_main = array();
        $data = $this->fetch_array($data);
        if(!empty($data)){
            $key = $data[1]['day'];
        }
        $array_temp = array();
        foreach ($data as $keyt =>$item){
            if($item['day'] == $key){
                $array_temp[] = $item;
                if($keyt == count($data)){
                    $array_main[$key] = $array_temp;
                }
            }else {
                $array_main[$key] = $array_temp;
                $array_temp = [];
                $key = $item['day'];
                $array_temp[] = $item;
                if($keyt == count($data)){
                    $array_main[$key] = $array_temp;
                }
            }
        }
        return $array_main;
    }
}
