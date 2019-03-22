<?php
/**
 * QandA plugin for Craft CMS 3.x
 *
 * Question & Answers
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\qanda\records;

use kuriousagency\qanda\QandA;

use Craft;
use craft\db\ActiveRecord;

/**
 * @author    Kurious Agency
 * @package   QandA
 * @since     0.0.1
 */
class Question extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%qanda_questions}}';
    }
}
