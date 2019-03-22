<?php
/**
 * QandA plugin for Craft CMS 3.x
 *
 * Question & Answers
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\qanda\variables;

use kuriousagency\qanda\QandA;
use kuriousagency\qanda\elements\Question;
use kuriousagency\qanda\elements\db\QuestionQuery;

use Craft;
use yii\base\Behavior;

/**
 * @author    Kurious Agency
 * @package   QandA
 * @since     0.0.1
 */
class QandAVariable extends Behavior
{
    // Public Methods
    // =========================================================================

    public function init()
	{
		parent::init();

		//$this->reviews = Reviews::$plugin;
	}

	public function questions($criteria = null): QuestionQuery
	{
		$query = Question::find();
		if ($criteria) {
			Craft::configure($query, $criteria);
		}
		return $query;
	}
}
