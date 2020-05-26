<?php
namespace kuriousagency\qanda\fields;

use kuriousagency\qanda\elements\Question;

use Craft;
use craft\fields\BaseRelationField;

class Questions extends BaseRelationField
{
    // Public Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('qanda', 'Q and A');
    }

    protected static function elementType(): string
    {
        return Question::class;
    }

    public static function defaultSelectionLabel(): string
    {
        return Craft::t('qanda', 'Select a Q and A');
    }
}