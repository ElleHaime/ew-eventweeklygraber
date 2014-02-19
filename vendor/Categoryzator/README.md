Categoryzator

example:

include_once 'Categoryzator/autoload.php';

use \Categoryzator\Categoryzator;

$text = 'Big concert in london';

$cat = new Categoryzator($text);

// return array of possible categories
$obj = $cat->analiz(Categoryzator::MULTI_CATEGORY);

print_r($obj->category);