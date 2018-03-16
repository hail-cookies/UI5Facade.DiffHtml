<?php
namespace exface\OpenUI5Template\Template;

use exface\Core\Templates\AbstractAjaxTemplate\AbstractAjaxTemplate;
use exface\OpenUI5Template\Template\Elements\ui5AbstractElement;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\Core\CommonLogic\UxonObject;
use exface\Core\Interfaces\DataTypes\DataTypeInterface;
use exface\Core\Templates\AbstractAjaxTemplate\Formatters\JsDateFormatter;
use exface\OpenUI5Template\Template\Formatters\ui5DateFormatter;
use exface\OpenUI5Template\Template\Formatters\ui5TransparentFormatter;
use exface\Core\DataTypes\TimestampDataType;
use exface\OpenUI5Template\Template\Formatters\ui5DateTimeFormatter;
use exface\Core\Templates\AbstractAjaxTemplate\Formatters\JsBooleanFormatter;
use exface\OpenUI5Template\Template\Formatters\ui5BooleanFormatter;
use exface\Core\Templates\AbstractAjaxTemplate\Formatters\JsNumberFormatter;
use exface\OpenUI5Template\Template\Formatters\ui5NumberFormatter;

/**
 * 
 * @method ui5AbstractElement getElement()
 * 
 * @author Andrej Kabachnik
 *
 */
class OpenUI5Template extends AbstractAjaxTemplate
{

    protected $request_columns = array();
    
    /**
     * Cache for config key WIDGET.DIALOG.MAXIMIZE_BY_DEFAULT_IN_ACTIONS:
     * @var array [ action_alias => true/false ]
     */
    private $config_maximize_dialog_on_actions = [];

    public function init()
    {
        $this->setClassPrefix('ui5');
        $this->setClassNamespace(__NAMESPACE__);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\Core\Templates\AbstractAjaxTemplate\AbstractAjaxTemplate::processRequest($page_id=NULL, $widget_id=NULL, $action_alias=NULL, $disable_error_handling=false)
     */
    public function processRequest($page_id = NULL, $widget_id = NULL, $action_alias = NULL, $disable_error_handling = false)
    {
        $this->request_columns = $this->getWorkbench()->getRequestParams()['columns'];
        $this->getWorkbench()->removeRequestParam('columns');
        $this->getWorkbench()->removeRequestParam('search');
        $this->getWorkbench()->removeRequestParam('draw');
        $this->getWorkbench()->removeRequestParam('_');
        return parent::processRequest($page_id, $widget_id, $action_alias, $disable_error_handling);
    }

    public function getRequestPagingOffset()
    {
        if (! $this->request_paging_offset) {
            $this->request_paging_offset = $this->getWorkbench()->getRequestParams()['start'];
            $this->getWorkbench()->removeRequestParam('start');
        }
        return $this->request_paging_offset;
    }

    public function getRequestPagingRows()
    {
        if (! $this->request_paging_rows) {
            $this->request_paging_rows = $this->getWorkbench()->getRequestParams()['length'];
            $this->getWorkbench()->removeRequestParam('length');
        }
        return $this->request_paging_rows;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Templates\AbstractAjaxTemplate\AbstractAjaxTemplate::buildJs()
     */
    public function buildJs(\exface\Core\Widgets\AbstractWidget $widget)
    {
        $instance = $this->getElement($widget);
        $js = $instance->buildJs();
        return $js . ($js ? "\n" : '') . $instance->buildJsView();
    }
    
    /**
     * Returns TRUE if a dialog generated by the given action should be maximized by default
     * according to the current template configuration - and FALSE otherwise.
     * 
     * @param ActionInterface $action
     * @return boolean
     */
    public function getConfigMaximizeDialogByDefault(ActionInterface $action)
    {
        // Check the cache first.
        if (array_key_exists($action->getAliasWithNamespace(), $this->config_maximize_dialog_on_actions)) {
            return $this->config_maximize_dialog_on_actions[$action->getAliasWithNamespace()];
        }
        
        // If no cache hit, see if the action matches one of the action selectors from the config or
        // is derived from them. If so, return TRUE and cache the result to avoid having to do the
        // checks again for the next button with the same action. This saves a lot of checks as
        // generic actions like EditObjectDialog are often used for multiple buttons.
        $selectors = $this->getConfig()->getOption('WIDGET.DIALOG.MAXIMIZE_BY_DEFAULT_IN_ACTIONS');
        if ($selectors instanceof UxonObject) {
            foreach ($selectors as $selector) {
                if ($action->is($selector)) {
                    $this->config_maximize_dialog_on_actions[$action->getAliasWithNamespace()] = true;
                    return true;
                }
            }
        }
        
        // Cache FALSE results too.
        $this->config_maximize_dialog_on_actions[$action->getAliasWithNamespace()] = false;
        return false;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Templates\AbstractAjaxTemplate\AbstractAjaxTemplate::getDataTypeFormatter()
     */
    public function getDataTypeFormatter(DataTypeInterface $dataType)
    {
        $formatter = parent::getDataTypeFormatter($dataType);
        
        switch (true) {
            case $formatter instanceof JsBooleanFormatter:
                return new ui5BooleanFormatter($formatter);
                break;
            case ($formatter instanceof JsNumberFormatter) && $formatter->getDataType()->getBase() === 10:
                return new ui5NumberFormatter($formatter);
                break;
            case $formatter instanceof JsDateFormatter:
                if ($formatter->getDataType() instanceof TimestampDataType) {
                    return new ui5DateTimeFormatter($formatter);
                } else {
                    return new ui5DateFormatter($formatter);
                }
                break;
        }
        
        return new ui5TransparentFormatter($formatter);
    }
    
    public function getUrlRoutePatterns() : array
    {
        return [
            "/[\?&]tpl=ui5/",
            "/\/api\/ui5[\/?]/"
        ];
    }
}
?>