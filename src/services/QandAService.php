<?php
/**
 * QandA plugin for Craft CMS 3.x
 *
 * Question & Answers
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\qanda\services;

use kuriousagency\qanda\QandA;
use kuriousagency\qanda\elements\Question;

use Craft;
use craft\base\Component;

/**
 * @author    Kurious Agency
 * @package   QandA
 * @since     0.0.1
 */
class QandAService extends Component
{
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     */
    public function getQuestionById(int $id, $siteId = null)
	{
		$question = Craft::$app->getElements()->getElementById($id, Question::class, $siteId);

		return $question;
    }
}
