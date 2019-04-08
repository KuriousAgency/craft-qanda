<?php
/**
 * QandA plugin for Craft CMS 3.x
 *
 * Question & Answers
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\qanda\elements;

use kuriousagency\qanda\QandA;
use kuriousagency\qanda\records\Question as QuestionRecord;
use kuriousagency\qanda\elements\db\QuestionQuery;
use kuriousagency\qanda\elements\actions\DeleteQuestion;
use kuriousagency\qanda\elements\actions\EnableQuestion;
use kuriousagency\qanda\elements\actions\DisableQuestion;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\commerce\Plugin as Commerce;
use craft\commerce\elements\Product;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\ElementHelper;
use craft\helpers\UrlHelper;
use craft\validators\DateTimeValidator;

/**
 * @author    Kurious Agency
 * @package   QandA
 * @since     0.0.1
 */
class Question extends Element
{
	// Constants
    // =========================================================================

    const STATUS_ENABLED = 'enabled';
    const STATUS_DISABLED = 'disabled';
	
	// Public Properties
    // =========================================================================

    /**
     * @var string
     */
	public $question;
	public $answer;
	public $customerId;
	public $productId;
	public $enabled;

	private $_email;
	private $_firstName;
	private $_lastName;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('qanda', 'Q&A');
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return false;
	}
	
	public static function hasStatuses(): bool
	{
		return true;
	}

	public static function statuses(): array
	{
		return [
			self::STATUS_ENABLED => Craft::t('qanda', 'Enabled'),
            self::STATUS_DISABLED => Craft::t('qanda', 'Disabled'),
		];
	}

	public function getStatus()
	{
		//$status = parent::getStatus();

		if ($this->enabled) {
			return self::STATUS_ENABLED;
		}

		return self::STATUS_DISABLED;
	}

    /**
     * @inheritdoc
     */
    public static function find(): ElementQueryInterface
    {
        return new QuestionQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
			'*' => [
                'key' => '*',
				'label' => Craft::t('qanda', 'All Questions'),
				'defaultSort' => ['dateCreated', 'desc'],
			],
			'Product' => [
                'key' => 'product',
				'label' => Craft::t('qanda', 'Product Questions'),
				'criteria' => ['productId' => ':notempty:'],
				'defaultSort' => ['dateCreated', 'desc'],
			],
			'General' => [
                'key' => 'general',
				'label' => Craft::t('qanda', 'General Questions'),
				'criteria' => ['productId' => ':empty:'],
				'defaultSort' => ['dateCreated', 'desc'],
            ]
		];

        return $sources;
	}
	
	protected static function defineActions(string $source = null): array
	{
		$actions = [];

		$actions[] = EnableQuestion::class;
		$actions[] = DisableQuestion::class;

		$deleteAction = Craft::$app->getElements()->createAction([
			'type' => DeleteQuestion::class,
			'confirmationMessage' => Craft::t('qanda', 'Are you sure you want to delete the selected questions?'),
			'successMessage' => Craft::t('qanda', 'Questions deleted.'),
		]);
		$actions[] = $deleteAction;

		return $actions;
	}

	protected static function defineSortOptions(): array
	{
		return [
			'dateCreated' => Craft::t('qanda', 'Date Created'),
			'email' => Craft::t('qanda', 'Email'),
			//'product' => ['label' => Craft::t('qanda', 'Product'), 'attribute' => 'product.title'],
			'firstName' => Craft::t('qanda', 'Firstname'),
			'lastName' => Craft::t('qanda', 'Lastname'),
		];
	}

	protected static function defineTableAttributes(): array
    {
		return [
			'id' => ['label' => Craft::t('qanda', 'ID')],
			'email' => ['label' => Craft::t('qanda', 'Email')],
			'firstName' => ['label' => Craft::t('qanda', 'Firstname')],
			'lastName' => ['label' => Craft::t('qanda', 'Lastname')],
			'email' => ['label' => Craft::t('qanda', 'Email')],
			'product' => ['label' => Craft::t('qanda', 'Product')],
			'dateCreated' => ['label' => Craft::t('qanda', 'Date Created')],
			'dateUpdated' => ['label' => Craft::t('qanda', 'Date Updated')],
		];
	}

	protected static function defineDefaultTableAttributes(string $source): array
    {
		return [
			'id',
			'email',
			'product',
			'dateCreated',
		];
	}

	protected static function defineSearchableAttributes(): array
    {
		return [
			'email',
			'firstName',
			'lastName',
			'dateCreated',
			'product',
		];
	}

	public function getSearchKeywords(string $attribute): string
    {
        switch ($attribute) {
            case 'product':
                return $this->product->title ?? '';
            default:
                return parent::getSearchKeywords($attribute);
        }
	}
	
	protected function tableAttributeHtml(string $attribute): string
    {
		switch ($attribute) {
			case 'product':
				{
					if (!$this->productId) {
						return '';
					}
					return '<a href="'.$this->product->cpEditUrl.'"><span class="status '.$this->product->status.'"></span>'.$this->product->title.'</a>';
				}
			default:
                {
                    return parent::tableAttributeHtml($attribute);
                }
		}
	}

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
			[['customerId', 'productId'], 'number', 'integerOnly' => true],
			[['enabled'], 'boolean'],
			['enabled', 'default', 'value' => false],
			[['question', 'email'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return true;
	}
	
	public function getCpEditUrl(): string
    {
        return UrlHelper::cpUrl('qanda/' . $this->id);
    }

    public function getCustomer()
	{
		if (!$this->customerId) {
			return null;
		}
		return Commerce::getInstance()->getCustomers()->getCustomerById($this->customerId);
	}

	public function getProduct()
	{
		if (!$this->productId) {
			return null;
		}
		return Commerce::getInstance()->getProducts()->getProductById($this->productId);
	}

	public function getEmail(): string
	{
		if ($this->getCustomer() && $this->getCustomer()->getUser()) {
			$this->setEmail($this->getCustomer()->getUser()->email);
		}

		return $this->_email ?? '';
	}

	public function setEmail($value)
    {
        $this->_email = $value;
	}
	
	public function getFirstName(): string
	{
		if ($this->getCustomer() && $this->getCustomer()->getUser()) {
			$this->setFirstName($this->getCustomer()->getUser()->firstName);
		} 

		return $this->_firstName ?? '';
	}

	public function setFirstName($value)
    {
        $this->_firstName = $value;
	}
	
	public function getLastName(): string
	{
		if ($this->getCustomer() && $this->getCustomer()->getUser()) {
			$this->setLastName($this->getCustomer()->getUser()->lastName);
		} 

		return $this->_lastName ?? '';
	}

	public function setLastName($value)
    {
        $this->_lastName = $value;
	}
	

    // Indexes, etc.
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    /*public function getEditorHtml(): string
    {
        $html = Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'textField', [
            [
                'label' => Craft::t('app', 'Title'),
                'siteId' => $this->siteId,
                'id' => 'title',
                'name' => 'title',
                'value' => $this->title,
                'errors' => $this->getErrors('title'),
                'first' => true,
                'autofocus' => true,
                'required' => true
            ]
        ]);

        $html .= parent::getEditorHtml();

        return $html;
    }*/

    // Events
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
		if (!$isNew) {
            $record = QuestionRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid question ID: ' . $this->id);
            }
        } else {
            $record = new QuestionRecord();
            $record->id = $this->id;
		}
		
		$record->question = $this->question;
		$record->answer = $this->answer;
		$record->customerId = $this->customerId;
		$record->productId = $this->productId;
		$record->email = $this->getEmail();
		$record->firstName = $this->getFirstName();
		$record->lastName = $this->getLastName();
		$record->enabled = $this->enabled;

		$record->save();

		$this->id = $record->id;

		return parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
    }

}
