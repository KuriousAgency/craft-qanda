<?php

namespace kuriousagency\qanda\migrations;

use kuriousagency\qanda\QandA;
use kuriousagency\qanda\elements\Question;

use Craft;
use craft\db\Migration;
use craft\db\Query;

/**
 * m200724_131105_add_relations_table migration.
 */
class m200724_131106_add_relations_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        
        $fieldService = Craft::$app->getFields();
        
        $fieldQuery = (new Query())
            ->select(['id'])
            ->from('{{%fields}}')
            ->where(['handle' => 'product'])
            ->one();

        $field = $fieldService->getFieldById($fieldQuery['id']);

        $layout = $fieldService->assembleLayout(['Relations' => [$field->id]],[]);
        $layout->type = Question::class;

        if(!$fieldService->saveLayout($layout)) {
            echo "Couldn't save q and a layout.\n";
			return false;
        }

        $questionQuery = (new Query())
            ->select(['id','productId'])
            ->from('{{%qanda_questions}}')
            ->all();
        
        foreach ($questionQuery as $row) {
            // $question = QandA::$plugin->service->getQuestionById($row['id']);
            if ($row['productId']) {
                echo 'QandA: ' . $row['productId']."\n";
                $this->insert('{{%relations}}',['fieldId' => $field->id,'sourceId' => $row['id'], 'targetId' => $row['productId']]);
            }
            $this->insert('{{%content}}',['elementId' => $row['id'],'siteId' => 1]);
        }
        

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200724_131105_add_relations_table cannot be reverted.\n";
        return false;
    }
}
