<?php
/**
 * Class Categoryzator
 *
 * @author   Slava Basko <basko.slava@gmail.com>
 */

namespace Categoryzator;

use Categoryzator\Core\Analizator;
use Categoryzator\Core\CategoryzatorException;
use Categoryzator\Core\Text;

class Categoryzator {

    const SINGLE_CATEGORY = 1;

    const MULTI_CATEGORY = 2;

    private $Text = null;

    /**
     * @param Text $Text
     */
    public function __construct(Text $Text)
    {
        $this->Text = $Text;
    }

    /**
     * Return STRING single category or ARRAY of categories
     *
     * @param int $type
     * @return array|null
     */
    public function analiz($type = self::SINGLE_CATEGORY)
    {
        try {
            $analiz = new Analizator($this->Text, $type);
            $text =  $analiz->doAnaliz();
        }catch (CategoryzatorException $e) {
            exit($e->getMsg());
        }
        return $text;
    }

}
