<?php
/**
 * Class Analizator
 *
 * @author   Slava Basko <basko.slava@gmail.com>
 */

namespace Categoryzator\Core;


class Analizator {

    /**
     * @var Text string
     */
    private $Text = null;

    /**
     * Type - return single or multiple category
     *
     * @var null
     */
    private $analizType = null;

    /**
     * Array of categories
     *
     * @var array|mixed
     */
    private $categories = array();

    /**
     * Categories entriy holder
     *
     * @var array
     */
    private $entries = array();

    /**
     * Default category key
     *
     * @var string
     */
    private $defaultCategory = 'other';

    /**
     * Get categories and set analiz type
     *
     * @param Text $text
     * @param $analizType
     * @throws CategoryzatorException
     */
    public function __construct(Text $text, $analizType)
    {
        if (!$text instanceof Text) {
            throw new CategoryzatorException('Param $text must be a instance of Categoryzator\Core\Text object');
        }
        $this->Text = $text;

        $this->analizType = $analizType;

        $cat = new Parser();
        $this->categories = $cat->getCategories();
    }

    /**
     * Category detect handler
     *
     * @return array|null
     */
    public function doAnaliz()
    {
        // WARNING --> keep order
        $this->countEntry();
        $this->searchCategory();
        //
        return $this->Text;
    }

    /**
     * Detect categories entry in text
     */
    private function countEntry()
    {
        $this->Text->tag = array();

        foreach ($this->Text->content as $content) {

            foreach ($this->categories as $categoryName => $tags) {

                $insert = function($category, $tag) {
                    if (array_key_exists($category, $this->entries)) {
                        $this->entries[$category]['entry']++;
                    }else {
                        $this->entries[$category] = array(
                            'key' => $category,
                            'entry' => 1
                        );
                    }

                    if (!array_key_exists($category, $this->Text->tag) || !array_key_exists($tag, $this->Text->tag[$category])) {
                        $this->Text->tag[$category][] = $tag;
                    }
                };

                foreach ($tags as $key => $val) {
                    if (is_string($val)) {
                        preg_match('/'.$val.'/i', $content, $output);
                        if (count($output) > 0) {
                            $insert($categoryName, $val);
                        }
                    } elseif (is_array($val)) {
                        preg_match('/'.$key.'/i', $content, $output);
                        if (count($output) > 0) {
                            $insert($categoryName, $key);
                        }
                        foreach ($val as $subCat) {
                            preg_match('/'.$subCat.'/i', $content, $output);
                            if (count($output) > 0) {
                                $insert($categoryName, $key);
                            }
                        }
                    }
                }

            }

        }

        $this->Text->countEntry = $this->entries;
    }

    /**
     * Return STRING single category or ARRAY of categories
     *
     * @return array|null
     */
    private function searchCategory()
    {
        $categories = array_values($this->entries);
        $category = $this->defaultCategory;

        if ($this->analizType === 1) {
            $tmpIndex = 0;
            $tmpMax = 0;
            if (!empty($this->entries)) {
                foreach ($categories as $index => $node) {

                    if ($node['entry'] > $tmpMax) {
                        $tmpMax = $node['entry'];
                        $tmpIndex = $index;
                    }

                }
                $category = $categories[$tmpIndex]['key'];
            }
        }
        unset($tmpIndex);
        unset($tmpMax);
        unset($index);

        if ($this->analizType === 2) {
            if (empty($categories)) {
                $category = array($this->defaultCategory);
            }else {
                $category = array();
            }
            foreach ($categories as $node) {
                $category[] = $node['key'];
            }
        }
        unset($node);

        $this->Text->category = $category;
    }

} 