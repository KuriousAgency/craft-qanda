<?php
/**
 * QandA plugin for Craft CMS 3.x
 *
 * Question & Answers
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\qanda\elements\actions;

use kuriousagency\qanda\QandA;

use Craft;
use craft\base\ElementAction;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Json;

/**
 * Class Disable
 *
 * @property null|string $triggerHtml the action’s trigger HTML
 * @property string $triggerLabel the action’s trigger label
 */

class DeleteQuestion extends Delete
{
    // Public Properties
    // =========================================================================


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query = null): bool
    {
        if (!$query) {
            return false;
        }

        foreach ($query->all() as $question) {
            Craft::$app->getElements()->deleteElement($question);
        }

        $this->setMessage(Craft::t('reviews', 'Questions deleted.'));

        return true;
    }
}
