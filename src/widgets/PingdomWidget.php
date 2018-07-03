<?php
/**
 * Pingdom plugin for Craft CMS 3.x
 *
 * A Widget To Show Stats From Pingdom On The Dashboard
 *
 * @link      https://adamisntdead.com
 * @copyright Copyright (c) 2018 Adam Kelly
 */

namespace adamisntdead\pingdom\widgets;

use adamisntdead\pingdom\Pingdom;
use adamisntdead\pingdom\assetbundles\pingdomwidgetwidget\PingdomWidgetWidgetAsset;

use Craft;
use craft\base\Widget;

/**
 * Pingdom Widget
 *
 * https://craftcms.com/docs/plugins/widgets
 *
 * @author    Adam Kelly
 * @package   Pingdom
 * @since     1.0.0
 */
class PingdomWidget extends Widget
{

    // Public Properties
    // =========================================================================

    public $email = '';
    public $password = '';
    public $app_key = '';
    public $website = '';

    private $check_id;

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('pingdom', 'Pingdom');
    }

    /**
     * Returns the path to the widget’s SVG icon.
     *
     * @return string|null The path to the widget’s SVG icon
     */
    public static function iconPath()
    {
        return Craft::getAlias("@adamisntdead/pingdom/assetbundles/pingdomwidgetwidget/dist/img/PingdomWidget-icon.svg");
    }

    /**
     * Returns the widget’s maximum colspan.
     *
     * @return int|null The widget’s maximum colspan, if it has one
     */
    public static function maxColspan()
    {
        return null;
    }

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules = array_merge(
            $rules,
            [
                [['email', 'password', 'app_key', 'website'], 'string'],
                [['email', 'password', 'app_key', 'website'], 'required'],
                ['email', 'email']
            ]
        );
        return $rules;
    }

    /**
     * Returns the component’s settings HTML.

     * @return string|null
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'pingdom/_components/widgets/PingdomWidget_settings',
            [
                'widget' => $this
            ]
        );
    }

    /**
     * Returns the widget's body HTML.
     *
     * @return string|false The widget’s body HTML, or `false` if the widget
     *                      should not be visible. (If you don’t want the widget
     *                      to be selectable in the first place, use {@link isSelectable()}.)
     */
    public function getBodyHtml()
    {
        Craft::$app->getView()->registerAssetBundle(PingdomWidgetWidgetAsset::class);

        $pingdom = new \Pingdom\Client($this->email, $this->password, $this->app_key);

        if (!isset($this->check_id))
        {
            $checks = $pingdom->getChecks();
            $checksWebsites = array_column($checks, 'id', 'hostname');
            if (!isset($checksWebsites[$this->website]))
            {
                return Craft::$app->getView()->renderTemplate(
                    'pingdom/_components/widgets/PingdomWidget_body',
                    [
                        'error' => true
                    ]
                ); 
            }
            $this->check_id = $checksWebsites[$this->website];
        }

        // Get performance summary
        $performance = $pingdom->getPerformanceSummary($this->check_id, "week");
        // Get last full week
        $performance = $performance[count($performance) - 2];
        
        // 604800 seconds in a week
        $uptime = 100 * $performance['uptime'] / 604800;

        return Craft::$app->getView()->renderTemplate(
            'pingdom/_components/widgets/PingdomWidget_body',
            [
                'error' => false,
                'uptime' => round($uptime, 2),
                'avgresponse' => $performance['avgresponse'] 
            ]
        );
    }

    /**
     * Validates Pingdom Details
     */
    private function validatePingdom($attribute, $params)
    {
        try {
            $website = $this->website;

            $pingdom = new \Pingdom\Client($this->email, $this->password, $this->app_key);
    
            $checks = $pingdom->getChecks();
            $checksWebsites = array_column($checks, 'id', 'hostname');
    
            if (!isset($checksWebsites[$website]))
            {
                throw new Exception('Website Not There m8');
            } else {
                $this->check_id = $checksWebsites[$website];
            } 
        } catch (Exception $e) {
            $this->addError('username', "Please Check Your Details");
        }
        
    }
}
