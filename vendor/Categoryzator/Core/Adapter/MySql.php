<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 3/20/14
 * Time: 10:25 AM
 */

namespace Categoryzator\Core\Adapter;

use Categoryzator\Core\TConfig;

class MySql extends AbstractAdapter
{

    use TConfig;

    private $connection = null;

    public function __construct()
    {
        $this->initializeConfig();
        $this->connection = new \PDO("mysql:host={$this->config->database->host};dbname={$this->config->database->database};charset=utf8", $this->config->database->user, $this->config->database->password);
    }

    /**
     * Return array of categories
     *
     * @return array|mixed
     */
    public function getCategories()
    {
        $this->prepareCategories();
        return $this->categories;
    }

    private function prepareCategories()
    {
        $sortedCategories = [];

        $QueryCategory = $this->connection->query('SELECT * FROM '.$this->config->database->table_category);
        $categories = $QueryCategory->fetchAll(\PDO::FETCH_ASSOC);

        $QueryTag = $this->connection->query('SELECT * FROM '.$this->config->database->table_tag);
        $tags = $QueryTag->fetchAll(\PDO::FETCH_ASSOC);

        $QueryKeyword = $this->connection->query('SELECT * FROM '.$this->config->database->table_keyword);
        $keywords = $QueryKeyword->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($categories as $categoryNode) {
            $sortedCategories[$categoryNode['key']] = [];

            foreach ($tags as $tagNode) {
                if ($categoryNode['id'] == $tagNode['category_id']) {
                    $sortedCategories[$categoryNode['key']][$tagNode['name']] = [];

                    foreach ($keywords as $keywordNode) {
                        if ($tagNode['id'] == $keywordNode['tag_id']) {
                            $sortedCategories[$categoryNode['key']][$tagNode['name']][] = $keywordNode['key'];
                        }
                    }
                }
            }
        }

        $this->categories = $sortedCategories;
    }

} 