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
class m200724_131105_add_relations_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $fieldQuery = (new Query())
            ->select(['id'])
            ->from('{{%fields}}')
            ->where(['handle' => 'product'])
            ->one();
        $field = Craft::$app->getFields()->getFieldById($fieldQuery['id']);

        $layout = Craft::$app->getFields()->assembleLayout(['Relations' => [$field->id]],[]);
        $layout->type = Question::class;
        Craft::$app->getFields()->saveLayout($layout);

        $questionQuery = (new Query())
            ->select(['id','productId'])
            ->from('{{%qanda_questions}}')
            ->all();

        
        // $this->dropForeignKey(
        //     $this->db->getForeignKeyName(
        //         '{{%qanda_questions}}',
        //         'productId',
        //         false
        //     ),
        //     '{{%qanda_questions}}'
        // );  
        // $this->dropIndex(
        //     $this->db->getIndexName(
        //         '{{%qanda_questions}}',
        //         'productId',
        //         false
        //     ),
        //     '{{%qanda_questions}}'
        // );        
        // $this->dropColumn('{{%qanda_questions}}','productId');
        // $this->addColumn('{{%qanda_questions}}','hasRelation',$this->boolean());
        
        foreach ($questionQuery as $row) {
            // $question = QandA::$plugin->service->getQuestionById($row['id']);
            if ($row['productId']) {
                // Craft::$app->getRelations()->saveRelations($field,$question,[$row['productId']]);
                // $hasRelation = $row['productId'] ? true : null;
                $this->insert('{{%relations}}',['fieldId' => $field->id,'sourceId' => $row['id'], 'targetId' => $row['productId']]);
            }
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
