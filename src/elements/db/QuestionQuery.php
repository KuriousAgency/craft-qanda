<?php
/**
 * QandA plugin for Craft CMS 3.x
 *
 * Question & Answers
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\qanda\elements\db;

use kuriousagency\qanda\QandA;

use Craft;
use craft\base\Element;
use craft\db\Query;
use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
use craft\commerce\Plugin as Commerce;
use craft\elements\User;
use craft\commerce\elements\Product;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use DateTime;
use yii\db\Connection;
use yii\db\Expression;


class QuestionQuery extends ElementQuery
{
    // Properties
    // =========================================================================

	public $question;
	
	public $answer;

	public $customerId;
	
	public $relatedIds;

	public $email;

	public $firstName;

	public $lastName;

	public $enabled;

	public $dateCreated;

	public $dateUpdated;


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct(string $elementType, array $config = [])
    {
        // Default orderBy
        if (!isset($config['orderBy'])) {
            $config['orderBy'] = 'qanda_questions.dateCreated DESC';
        }

        parent::__construct($elementType, $config);
    }

    /**
     * @inheritdoc
     */
    /*public function __set($name, $value)
    {
        switch ($name) {
            case 'createdAfter':
                $this->createdAfter($value);
				break;
			case 'createdBefore':
                $this->createdBefore($value);
                break;
            default:
                parent::__set($name, $value);
        }
	}*/

	public function customerId($value = null)
	{
		$this->customerId = $value;
		return $this;
	}

	public function email($value = null)
	{
		$this->email = $value;
		return $this;
	}

	public function relatedIds($value = null)
	{
		$this->relatedIds = $value;
		return $this;
	}

	public function enabled($value = null)
	{
		$this->enabled = $value;
		return $this;
	}

	public function customer($value = null)
	{
		if ($value) {
            $this->customerId = $value->id;
        } else {
            $this->customerId = null;
        }

        return $this;
	}

	public function user($value)
    {
        if ($value instanceof User) {
            $customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($value->id);
            $this->customerId = $customer->id ?? null;
        } else if ($value !== null) {
            $customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($value);
            $this->customerId = $customer->id ?? null;
        } else {
            $this->customerId = null;
        }

        return $this;
	}
	
	public function relatedElements($value)
	{
		if (is_array($value)) {
			foreach ($value as $v) {
				if ($v instanceof Element) {
					$element = Element::find()->id($value->id)->one();
					$this->relatedIds[] = $element->id ?? null;
				} else if ($value !== null) {
					$element = Element::find()->id($value->id)->one();
					$this->relatedIds[] = $element->id ?? null;
				}
			}
		} else if ($v instanceof Element) {
			$element = Element::find()->id($value->id)->one();
			$this->relatedIds[] = $element->id ?? null;
		} else if ($value !== null) {
			$element = Element::find()->id($value->id)->one();
			$this->relatedIds[] = $element->id ?? null;
		} else {
			$this->relatedIds = null;
		}

		return $this;
	}




    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {

        $this->joinElementTable('qanda_questions');

        $this->query->select([
			'qanda_questions.id',
			'qanda_questions.question',
			'qanda_questions.answer',
			'qanda_questions.customerId',
			'qanda_questions.email',
			'qanda_questions.firstName',
			'qanda_questions.lastName',
			'qanda_questions.enabled',
			'qanda_questions.dateCreated',
			'qanda_questions.dateUpdated',
        ]);


		if ($this->email) {
            $this->subQuery->andWhere(Db::parseParam('qanda_questions.email', $this->email));
		}
		
		if ($this->firstName) {
            $this->subQuery->andWhere(Db::parseParam('qanda_questions.firstName', $this->firstName));
		}
		
		if ($this->lastName) {
            $this->subQuery->andWhere(Db::parseParam('qanda_questions.lastName', $this->lastName));
		}
		
		if ($this->enabled) {
            $this->subQuery->andWhere(Db::parseParam('qanda_questions.enabled', $this->enabled));
		}
		
		if ($this->dateCreated) {
            $this->subQuery->andWhere(Db::parseDateParam('qanda_questions.dateCreated', $this->dateCreated));
		}
		
		if ($this->dateUpdated) {
            $this->subQuery->andWhere(Db::parseDateParam('qanda_questions.dateUpdated', $this->dateUpdated));
		}
		
		if ($this->customerId) {
            $this->subQuery->andWhere(Db::parseParam('qanda_questions.customerId', $this->customerId));
		}
		
		if ($this->relatedIds) {

			$query = (new Query())
				->select('qanda_questions.id')
				->distinct()
				->from('{{%qanda_questions}},{{%relations}}')
				->where('qanda_questions.id = relations.sourceId')
				->all();
			if ($this->relatedIds == ':empty:') {
				$param = Db::parseParam('qanda_questions.id', array_column($query , 'id'), 'not');
			} else {
				$param = Db::parseParam('qanda_questions.id', array_column($query , 'id'));
			}
			$this->subQuery->andWhere($param);

			// Craft::dd($this->subQuery->rawSql);
		}
		
		// Craft::dd($this->subQuery->rawSql);

		return parent::beforePrepare();
    }


}
