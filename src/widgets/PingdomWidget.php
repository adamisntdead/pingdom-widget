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
 * Dashboard widgets allow you to display information in the Admin CP Dashboard.
 * Adding new types of widgets to the dashboard couldn’t be easier in Craft
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
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
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
     *
     * An extremely simple implementation would be to directly return some HTML:
     *
     * ```php
     * return '<textarea name="foo">'.$this->getSettings()->foo.'</textarea>';
     * ```
     *
     * For more complex settings, you might prefer to create a template, and render it via
     * [[\craft\web\View::renderTemplate()]]. For example, the following code would render a template loacated at
     * craft/plugins/myplugin/templates/_settings.html, passing the settings to it:
     *
     * ```php
     * return Craft::$app->getView()->renderTemplate('myplugin/_settings', [
     *     'settings' => $this->getSettings()
     * ]);
     * ```
     *
     * If you need to tie any JavaScript code to your settings, it’s important to know that any `name=` and `id=`
     * attributes within the returned HTML will probably get [[\craft\web\View::namespaceInputs() namespaced]],
     * however your JavaScript code will be left untouched.
     *
     * For example, if getSettingsHtml() returns the following HTML:
     *
     * ```html
     * <textarea id="foo" name="foo"></textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('foo');
     * </script>
     * ```
     *
     * …then it might actually look like this before getting output to the browser:
     *
     * ```html
     * <textarea id="namespace-foo" name="namespace[foo]"></textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('foo');
     * </script>
     * ```
     *
     * As you can see, that JavaScript code will not be able to find the textarea, because the textarea’s `id=`
     * attribute was changed from `foo` to `namespace-foo`.
     *
     * Before you start adding `namespace-` to the beginning of your element ID selectors, keep in mind that the actual
     * namespace is going to change depending on the context. Often they are randomly generated. So it’s not quite
     * that simple.
     *
     * Thankfully, [[\craft\web\View]] service provides a couple handy methods that can help you deal
     * with this:
     *
     * - [[\craft\web\View::namespaceInputId()]] will give you the namespaced version of a given ID.
     * - [[\craft\web\View::namespaceInputName()]] will give you the namespaced version of a given input name.
     * - [[\craft\web\View::formatInputId()]] will format an input name to look more like an ID attribute value.
     *
     * So here’s what a getSettingsHtml() method that includes field-targeting JavaScript code might look like:
     *
     * ```php
     * public function getSettingsHtml()
     * {
     *     // Come up with an ID value for 'foo'
     *     $id = Craft::$app->getView()->formatInputId('foo');
     *
     *     // Figure out what that ID is going to be namespaced into
     *     $namespacedId = Craft::$app->getView()->namespaceInputId($id);
     *
     *     // Render and return the input template
     *     return Craft::$app->getView()->renderTemplate('myplugin/_fieldinput', [
     *         'id'           => $id,
     *         'namespacedId' => $namespacedId,
     *         'settings'     => $this->getSettings()
     *     ]);
     * }
     * ```
     *
     * And the _settings.html template might look like this:
     *
     * ```twig
     * <textarea id="{{ id }}" name="foo">{{ settings.foo }}</textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('{{ namespacedId }}');
     * </script>
     * ```
     *
     * The same principles also apply if you’re including your JavaScript code with
     * [[\craft\web\View::registerJs()]].
     *
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
        // echo $this->email;
        // echo $this->password;
        // echo $this->app_key;

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
