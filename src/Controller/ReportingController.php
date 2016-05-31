<?php

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;


class ReportingController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->viewBuilder()->layout('reports');
    }

    public function index()
    {

        $query_date = date("Y-m-d");

        // $month_ini = strtotime("first day of last month");
        // $month_end = strtotime("last day of last month");

        $conn = ConnectionManager::get('default');

        $startDate = date('Y-m-01', strtotime($query_date)).' '.'00:00:00';
        $endDate = date('Y-m-t', strtotime($query_date)).' '.'23:59:59';

        // $startDate = date( "Y-m-d", $month_ini).'00:00:00';
        // $endDate = date( "Y-m-d", $month_end).'23:59:59';

        $totalNewMembers = $conn->execute('SELECT count(id) as TotalNewCustomers FROM customers WHERE renewed = 0 AND created > "'.$startDate.'" AND created < "'.$endDate.'"');
        $newMemberResults = $totalNewMembers->fetchAll('assoc');
        $this->set('totalNewCustomers', $newMemberResults[0]);

        $customerDataDaily = $conn->execute('SELECT 
            DAY(created) AS Day, 
            COUNT(*) as Total 
            FROM customers
            WHERE created > "'.$startDate.'" AND created < "'.$endDate.'" AND renewed = 0
            GROUP BY DAY(created)
            ORDER BY DAY(created)
        ');

        $resultsDaily = $customerDataDaily->fetchAll('assoc');

        if(count($resultsDaily) > 0)
            $this->set('customersDaily', $resultsDaily);
        else
            $this->set('customersDaily', NULL);

        
        //

        $customerData = $conn->execute('SELECT 
            DAYNAME(created) as Weekday,
            COUNT(*) as Total 
            FROM customers
            WHERE created > "'.$startDate.'" AND created < "'.$endDate.'" AND renewed = 0
            GROUP BY DAYOFWEEK(created)
            ORDER BY DAYOFWEEK(created)
        ');

        $results = $customerData->fetchAll('assoc');

        if(count($results) > 0)
        {
            foreach($results as $result)
                $newObject[$result['Weekday']]['Total'] = $result['Total'];

            $this->set('customersWeeklyDaily', $newObject);
        }
        else
            $this->set('customersWeeklyDaily', NULL);

        $customerDataWeekly = $conn->execute('SELECT 
            FLOOR((DayOfMonth(created) - 1) / 7) + 1 as Week, 
            COUNT(*) as Total
            FROM customers
            WHERE created > "'.$startDate.'" AND created < "'.$endDate.'" AND renewed = 0
            GROUP BY Week
            ORDER BY Week, DAYOFWEEK(created)
        ');

        $resultsWeekly = $customerDataWeekly->fetchAll('assoc');

        if(count($resultsWeekly) > 0)
            $this->set('customersWeekly', $resultsWeekly);
        else
            $this->set('customersWeekly', NULL);


        if(isset($this->request->query['export']) && $this->request->query['export'] == 1)
        {
            $filename = "website_data_" . date('Ymd') . ".xls";
            //header("Content-Type: text/plain");

            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");

            $flag = false;

            foreach($newMemberResults as $row) 
            {
                if(!$flag) 
                {
                    $data = "Total New Customers";

                    echo $data . "\r\n\n";
                    // display field/column names as first row
                    echo implode("\t", array_keys($row)) . "\r\n";
                    $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n";
            }

            echo "\r\n\n";
            $flag = false;
            foreach($resultsDaily as $row) 
            {
                if(!$flag) 
                {
                    $data = "New Customers Daily";

                    echo $data . "\r\n\n";
                    // display field/column names as first row
                    echo implode("\t", array_keys($row)) . "\r\n";
                    $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n";
            }

            echo "\r\n\n";
            $flag = false;
            foreach($results as $row)
            {
                if(!$flag)
                {
                    $data = "New Customers / Day Of the week";

                    echo $data . "\r\n\n";
                    echo implode("\t", array_keys($row)) . "\r\n";
                    $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n";
            }

            echo "\r\n\n";
            $flag = false;
            foreach($resultsWeekly as $row) 
            {
                if(!$flag) 
                {

                    $data = "New Customers Weekly";

                    echo $data . "\r\n\n";
                    // display field/column names as first row
                    echo implode("\t", array_keys($row)) . "\r\n";
                    $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n";
            }

            exit;
        }

    }

}
