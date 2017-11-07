<?php

require_once 'abstract.php';

class Mage_Shell_IndexCheck extends Mage_Shell_Abstract
{

    public function run()
    {
        $id = $_GET['id'];
        echo '<pre>';
        $resource   = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');

        $tables = array();
        $result = $connection->fetchAll('SHOW TABLES');
        foreach ($result as $item) {
            $keys = array_keys($item);
            $tables[] = $item[$keys[0]];
        }

        foreach ($tables as $table) {
            $columns = array_keys($connection->describeTable($table));
            
            $pk = false;
            if (in_array('entity_id', $columns)) {
                $pk = 'entity_id';
            }

            if (in_array('product_id', $columns)) {
                $pk = 'product_id';
            }

            if (!$pk) {
                continue;
            }

            $result = $connection->fetchAll("SELECT * FROM $table WHERE $pk = $id");
            
            $this->_output($table, $columns, $result);
        }
    }

    public function _output($table, $columns, $result)
    {
        $html = '<h3>'.$table.'</h3>';
        $html .= '<table border="1">';
        $html .= '<tr>';
        foreach ($columns as $column) {
            $html .= '<th>'.$column.'</th>';
        }
        $html .= '</tr>';

        foreach ($result as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>'.$value.'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';

        echo $html;
    }

    public function _validate()
    {
    }
}

$shell = new Mage_Shell_IndexCheck();
$shell->run();
