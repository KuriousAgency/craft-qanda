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
use kuriousagency\emaileditor\EmailEditor;
use craft\mail\Message;

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

    public function sendEmail($question)
    {

        $renderVariables = [
            'question' => $question,
            'handle' => 'qanda'
        ];            

        //What about if email editor isn't installed??

        if(Craft::$app->plugins->isPluginEnabled('email-editor')) {
            $templatePath = EmailEditor::$plugin->emails->getEmailByHandle($renderVariables['handle'])->template;
        } else {
            $templatePath = QandA::$plugin->getSettings()->templatePath;
        }        

        $view = Craft::$app->getView();
        $oldTemplateMode = $view->getTemplateMode();

        $view->setTemplateMode($view::TEMPLATE_MODE_SITE);

        if ($view->doesTemplateExist($templatePath) && $question->email) {

            $fromName = Craft::$app->systemSettings->getEmailSettings()->fromEmail;
            $fromName = Craft::parseEnv($fromName);

            $newEmail = new Message();
            $newEmail->setTo($question->email);
            $newEmail->setFrom($fromName);
            $newEmail->setSubject('Thanks for your Question');
            $newEmail->variables = $renderVariables;
            $body = $view->renderTemplate($templatePath, $renderVariables);
            $newEmail->setHtmlBody($body);
            // Craft::dd($newEmail);
            if (!Craft::$app->getMailer()->send($newEmail)) {
            
                $error = Craft::t('qandq', 'Email Error');
    
                Craft::error($error, __METHOD__);
                
                Craft::$app->language = $originalLanguage;
                $view->setTemplateMode($oldTemplateMode);

                return false;
            }

        } else {
            $error = Craft::t('qandq', 'Template not found for email with handle “{handle}”.', [
                'handle' => $renderVariables['handle']
            ]);

            Craft::error($error, __METHOD__);
        }
        
        $view->setTemplateMode($oldTemplateMode);



    }
}
