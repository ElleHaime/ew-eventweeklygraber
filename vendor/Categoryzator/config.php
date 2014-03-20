<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 3/20/14
 * Time: 10:31 AM
 */

$config = new stdClass();
$config->database = new stdClass();

// EDIT THIS
$config->adapter = 'mysql'; // adapters - 'file', 'mysql'
$config->database->host = 'localhost';
$config->database->user = 'root';
$config->database->password = 'pass';
$config->database->database = 'eventweekly_dev';

$config->database->table_category = 'category';
$config->database->table_tag = 'tag';
$config->database->table_keyword = 'keyword';
//

return $config;
