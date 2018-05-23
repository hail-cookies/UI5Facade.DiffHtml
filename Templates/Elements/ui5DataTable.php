<?php
namespace exface\OpenUI5Template\Templates\Elements;

use exface\Core\Widgets\DataTable;
use exface\Core\Widgets\DataColumn;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryDataTableTrait;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\Core\Interfaces\Actions\iReadData;
use exface\Core\Widgets\Button;
use exface\Core\Widgets\ButtonGroup;
use exface\Core\Widgets\MenuButton;

/**
 *
 * @method DataTable getWidget()
 *        
 * @author Andrej Kabachnik
 *        
 */
class ui5DataTable extends ui5AbstractElement
{
    use JqueryDataTableTrait;
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::init()
     */
    protected function init()
    {
        parent::init();
        if ($this->isWrappedInDynamicPage()) {
            $this->getTemplate()->getElement($this->getWidget()->getConfiguratorWidget())->setIncludeFilterTab(false);
        }
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\OpenUI5Template\Templates\Elements\ui5AbstractElement::buildJsConstructor()
     */
    public function buildJsConstructor($oControllerJs = 'oController') : string
    { 
        $controller = $this->getController();
        $controller->addProperty($this->getId() . '_pages', $this->buildJsPaginationObject());
        $controller->addMethod('onPaginate', $this, '', $this->buildJsPaginationRefresh());
        $controller->addMethod('onUpdateFilterSummary', $this, '', $this->buildJsFilterSummaryUpdater());
        $controller->addMethod('onLoadData', $this, 'oControlEvent, keep_page_pos, growing', $this->buildJsDataLoader());
        $controller->addDependentControl('oConfigurator', $this, $this->getTemplate()->getElement($this->getWidget()->getConfiguratorWidget()));
        $controller->addOnInitScript($this->buildJsRefresh(), $this->getId() . '_loadData');
        
        if ($this->isMTable()) {
            $js = $this->buildJsConstructorForMTable();
        } else {
            $js = $this->buildJsConstructorForUiTable();
        }
        
        if ($this->isWrappedInDynamicPage()){
            return $this->buildJsPage($js) . ".setModel(sap.ui.getCore().byId('{$this->getId()}').getModel())";
        } else {
            return $js;
        }
    }
    
    protected function isMTable()
    {
        return $this->getWidget()->isResponsive();
    }
    
    protected function isUiTable()
    {
        return ! $this->getWidget()->isResponsive();
    }
    
    /**
     * Returns the javascript constructor for a sap.m.Table
     * 
     * @return string
     */
    protected function buildJsConstructorForMTable()
    {
        $mode = $this->getWidget()->getMultiSelect() ? 'sap.m.ListMode.MultiSelect' : 'sap.m.ListMode.SingleSelectMaster';
        $striped = $this->getWidget()->getStriped() ? 'true' : 'false';
        
        return <<<JS
        new sap.m.Table("{$this->getId()}", {
    		fixedLayout: false,
            alternateRowColors: {$striped},
    		mode: {$mode},
            headerToolbar: [
                {$this->buildJsToolbar()}
    		],
    		columns: [
                {$this->buildJsColumnsForMTable()}
    		],
    		items: {
    			path: '/data',
                {$this->buildJsBindingOptionsForGrouping()}
                template: new sap.m.ColumnListItem({
                    type: "Active",
                    cells: [
                        {$this->buildJsCellsForMTable()}
                    ]
                }),
    		}
        })
        .setModel(new sap.ui.model.json.JSONModel())
        .attachItemPress(function(event){
            {$this->getOnChangeScript()}
        }){$this->buildJsClickListeners('oController')}

JS;
    }
            
    protected function buildJsBindingOptionsForGrouping()
    {
        $widget = $this->getWidget();
        
        if (! $widget->hasRowGroups()) {
            return '';
        }
        
        return <<<JS

                sorter: new sap.ui.model.Sorter(
    				'{$widget->getRowGrouper()->getGroupByColumn()->getDataColumnName()}', // sPath
    				false, // bDescending
    				true // vGroup
    			),
    			/*groupHeaderFactory: function(oGroup) {
                    // TODO add support for counters
                    return new sap.m.GroupHeaderListItem({
        				title: oGroup.key,
        				upperCase: false
        			});
                },*/
JS;
    }
    
    /**
     * Returns the javascript constructor for a sap.ui.table.Table
     * 
     * @return string
     */
    protected function buildJsConstructorForUiTable()
    {
        $widget = $this->getWidget();
        $controller = $this->getController();
        
        $selection_mode = $widget->getMultiSelect() ? 'sap.ui.table.SelectionMode.MultiToggle' : 'sap.ui.table.SelectionMode.Single';
        $selection_behavior = $widget->getMultiSelect() ? 'sap.ui.table.SelectionBehavior.Row' : 'sap.ui.table.SelectionBehavior.RowOnly';
        
        $js = <<<JS
            new sap.ui.table.Table("{$this->getId()}", {
        		visibleRowCountMode: sap.ui.table.VisibleRowCountMode.Auto,
                selectionMode: {$selection_mode},
        		selectionBehavior: {$selection_behavior},
                enableColumnReordering:true,
                enableColumnFreeze: true,
        		filter: {$controller->buildJsMethodCallFromView('onLoadData', $this)},
        		sort: {$controller->buildJsMethodCallFromView('onLoadData', $this)},
        		toolbar: [
        			{$this->buildJsToolbar()}
        		],
        		columns: [
        			{$this->buildJsColumnsForUiTable()}
        		],
                rows: "{/data}"
        	})
            .setModel(new sap.ui.model.json.JSONModel())
            .attachFirstVisibleRowChanged(function() {
                var pages = this.{$this->getId()}_pages;
                var lastVisibleRow = oTable.getFirstVisibleRow() + oTable.getVisibleRowCount();
                if ((pages.pageSize - lastVisibleRow <= 1) && (pages.end() + 1 !== pages.total)) {
                    pages.increasePageSize();
                    {$this->buildJsRefresh(true, true)}
                }
            }){$this->buildJsClickListeners('oController')}
JS;
            
        return $js;
    }
    
    /**
     * Returns a comma separated list of column constructors for sap.ui.table.Table
     * 
     * @return string
     */
    protected function buildJsColumnsForUiTable()
    {
        // Columns
        $column_defs = '';
        foreach ($this->getWidget()->getColumns() as $column) {
            $column_defs .= ($column_defs ? ", " : '') . $this->getTemplate()->getElement($column)->buildJsConstructorForUiColumn();
        }
        
        return $column_defs;
    }
    
    protected function buildJsCellsForMTable()
    {
        $cells = '';
        foreach ($this->getWidget()->getColumns() as $column) {
            $cells .= ($cells ? ", " : '') . $this->getTemplate()->getElement($column)->buildJsConstructorForCell();
        }
        
        return $cells;
    }
    
    /**
     * Returns a comma-separated list of column constructors for sap.m.Table
     * 
     * @return string
     */
    protected function buildJsColumnsForMTable()
    {
        $widget = $this->getWidget();
        
        // See if there are promoted columns. If not, make the first visible column promoted,
        // because sap.m.table would otherwise have not column headers at all.
        $promotedFound = false;
        $first_col = null;
        foreach ($widget->getColumns() as $col) {
            if (is_null($first_col) && ! $col->isHidden()) {
                $first_col = $col;    
            }
            if ($col->getVisibility() === EXF_WIDGET_VISIBILITY_PROMOTED && ! $col->isHidden()) {
                $promotedFound = true;
                break;
            }
        }
        
        if (! $promotedFound) {
            $first_col->setVisibility(EXF_WIDGET_VISIBILITY_PROMOTED);
        }

        $column_defs = '';
        foreach ($this->getWidget()->getColumns() as $column) {
            $column_defs .= ($column_defs ? ", " : '') . $this->getTemplate()->getElement($column)->buildJsConstructorForMColumn();
        }
        
        return $column_defs;
    }
    
    /**
     * Returns TRUE if this table uses a remote data source and FALSE otherwise.
     * 
     * @return boolean
     */
    protected function isLazyLoading()
    {
        return $this->getWidget()->getLazyLoading(true);
    }

    /**
     * Returns the definition of a javascript function to fill the table with data: onLoadDataTableId(oControlEvent).
     *  
     * @return string
     */
    protected function buildJsDataLoader($oControlEventJsVar = 'oControlEvent', $keepPagePosJsVar = 'keep_page_pos', $growingJsVar = 'growing')
    {
        if (! $this->isLazyLoading()) {
            return $this->buildJsDataLoaderOnClient($oControlEventJsVar, $keepPagePosJsVar, $growingJsVar);
        } else {
            return $this->buildJsDataLoaderOnServer($oControlEventJsVar, $keepPagePosJsVar, $growingJsVar);
        } 
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsDataLoaderOnClient($oControlEventJsVar = 'oControlEvent', $keepPagePosJsVar = 'keep_page_pos', $growingJsVar = 'growing')
    {
        $widget = $this->getWidget();
        $data = $widget->prepareDataSheetToRead($widget->getValuesDataSheet());
        if (! $data->isFresh()) {
            $data->dataRead();
        }
        
        // FIXME make filtering, sorting, pagination, etc. work in lazy mode too!
        
        return <<<JS

                try {
        			var data = {$this->getTemplate()->encodeData($this->prepareData($data, false))};
        		} catch (err){
                    console.error('Cannot load data into widget {$this->getId()}!');
                    return;
        		}
                sap.ui.getCore().byId("{$this->getId()}").getModel().setData(data);
    
JS;
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsDataLoaderOnServer($oControlEventJsVar = 'oControlEvent', $keepPagePosJsVar = 'keep_page_pos', $growingJsVar = 'growing')
    {
        $widget = $this->getWidget();
        $controller = $this->getController();
        
        $url = $this->getAjaxUrl();
        $params = '
					action: "' . $widget->getLazyLoadingActionAlias() . '"
					, resource: "' . $this->getPageId() . '"
					, element: "' . $widget->getId() . '"
					, object: "' . $widget->getMetaObject()->getId() . '"
				';
        
        return <<<JS

        		var oTable = sap.ui.getCore().byId("{$this->getId()}");
                var params = { {$params} };
        		var cols = oTable.getColumns();
        		var oModel = oTable.getModel();
                var oData = oModel.getData(); 
                var oController = this;
                
                oModel.attachRequestSent(function(){
        			{$this->buildJsBusyIconShow()}
        		});

                var fnCompleted = function(oEvent){
                    {$this->buildJsBusyIconHide()}
        			if (oEvent.getParameters().success) {
                        if (growing) {
                            var oDataNew = this.getData();
                            oDataNew.data = oData.data.concat(oDataNew.data);
                        }
                        oController.{$this->getId()}_pages.total = this.getProperty("/recordsFiltered");
                        {$controller->buildJsMethodCallFromController('onPaginate', $this, '', 'oController')};
                        
                        if (sap.ui.Device.system.phone) {
                            sap.ui.getCore().byId('{$this->getId()}_page').setHeaderExpanded(false);
                        }
                        
            			var footerRows = this.getProperty("/footerRows");
                        if (footerRows){
            				oTable.setFixedBottomRowCount(parseInt(footerRows));
            			}
                    } else {
                        var error = oEvent.getParameters().errorobject;
                        {$this->buildJsShowError('error.responseText', "(error.statusCode+' '+error.statusText)")}
                    }
                    
                    this.setProperty('/filterDescription', {$controller->buildJsMethodCallFromController('onUpdateFilterSummary', $this, '', 'oController')});
                    this.detachRequestCompleted(fnCompleted);
        		};
        
        		oModel.attachRequestCompleted(fnCompleted);
        		
        		// Add quick search
                params.q = sap.ui.getCore().byId('{$this->getId()}_quickSearch').getValue();
                
                // Add configurator data
                params.data = {$this->getP13nElement()->buildJsDataGetter()};
                
        		// Add pagination
                var pages = this.{$this->getId()}_pages;
                if (! {$keepPagePosJsVar}) {
                    pages.resetAll();
                }
                if ({$growingJsVar}) {
                    params.start = pages.growingLoadStart();
                    params.length = pages.growingLoadPageSize();
                } else {
                    params.start = pages.start;
                    params.length = pages.pageSize;
                }
        
                {$this->buildJsDataSourceColumnActions($oControlEventJsVar)}
                
                // Add sorters and filters from P13nDialog
                var aSortItems = sap.ui.getCore().byId('{$this->getP13nElement()->getIdOfSortPanel()}').getSortItems();
                for (var i in aSortItems) {
                    params.sort = (params.sort ? params.sort+',' : '') + aSortItems[i].getColumnKey();
                    params.order = (params.order ? params.order+',' : '') + (aSortItems[i].getOperation() == 'Ascending' ? 'asc' : 'desc');
                }
                
                oModel.loadData("{$url}", params);
    
JS;
    }
    
    protected function buildJsDataSourceColumnActions($oControlEventJsVar = 'oControlEvent')
    {
        if ($this->isMTable()) {
            return '';
        }
        
        return <<<JS

        // Add filters and sorters from column menus
		for (var i=0; i<oTable.getColumns().length; i++){
			var oColumn = oTable.getColumns()[i];
			if (oColumn.getFiltered()){
				params['fltr99_' + oColumn.getFilterProperty()] = oColumn.getFilterValue();
			}
		}
		
		// If sorting just now, make sure the sorter from the event is set too (eventually overwriting the previous sorting)
		if ({$oControlEventJsVar} && {$oControlEventJsVar}.getId() == 'sort'){
            sap.ui.getCore().byId('{$this->getP13nElement()->getIdOfSortPanel()}')
                .destroySortItems()
                .addSortItem(
                    new sap.m.P13nSortItem({
                        columnKey: {$oControlEventJsVar}.getParameters().column.getSortProperty(),
                        operation: {$oControlEventJsVar}.getParameters().sortOrder
                    })
                );
		}
		
		// If filtering just now, make sure the filter from the event is set too (eventually overwriting the previous one)
		if ({$oControlEventJsVar} && {$oControlEventJsVar}.getId() == 'filter'){
			params['fltr99_' + {$oControlEventJsVar}.getParameters().column.getFilterProperty()] = {$oControlEventJsVar}.getParameters().value;
		}

JS;
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsFilterSummaryUpdater()
    {
        $filter_checks = '';
        foreach ($this->getWidget()->getFilters() as $fltr) {
            if ($fltr->isHidden()) {
                continue;
            }
            $elem = $this->getTemplate()->getElement($fltr);
            $filter_checks .= 'if(' . $elem->buildJsValueGetter() . ") {filtersCount++; filtersList += (filtersList == '' ? '' : ', ') + '{$elem->getCaption()}';} \n";
        }
        return <<<JS
                var filtersCount = 0;
                var filtersList = '';
                {$filter_checks}
                if (filtersCount > 0) {
                    return '{$this->translate('WIDGET.DATATABLE.FILTERED_BY')} (' + filtersCount + '): ' + filtersList;
                } else {
                    return '{$this->translate('WIDGET.DATATABLE.FILTERED_BY')}: {$this->translate('WIDGET.DATATABLE.FILTERED_BY_NONE')}';
                }
JS;
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsFilterSummaryFunctionName() {
        return "{$this->buildJsFunctionPrefix()}CountFilters";
    }

	/**
     * Returns JavaScript-Functions which are necessary for the pagination.
     *
     * @return string
     */
    protected function buildJsPaginationObject()
    {
        $defaultPageSize = $this->getPaginationPageSize();
        
        return <<<JS
{
                	start: 0,
                    pageSize: {$defaultPageSize},
                    total: 0,
                    end: function() {
                        return Math.min(this.start + this.pageSize - 1, this.total - 1);
                    },
                    previous: function() {
                        this.resetPageSize();
                        if (this.start >= this.pageSize) {
                            this.start -= this.pageSize;
                        } else {
                            this.start = 0;
                        }
                    },
                    next: function() {
                        if (this.start < this.total - this.pageSize) {
                            this.start += this.pageSize;
                        }
                        this.resetPageSize();
                    },
                    increasePageSize: function() {
                        this.pageSize += {$defaultPageSize};
                    },
                    resetPageSize: function() {
                        this.pageSize = {$defaultPageSize};
                    },
                    resetAll: function() {
                        this.start = 0;
                        this.pageSize = {$defaultPageSize};
                        this.total = 0;
                    },
                    growingLoadStart: function() {
                        return this.start + this.pageSize - {$defaultPageSize};
                    },
                    growingLoadPageSize: function() {
                        return {$defaultPageSize};
                    }
                },

JS;
    }
    
    /**
     * 
     * @return string
     */
    protected function buildJsPaginationRefresh() : string
    {
        return <<<JS
                
                    var pages = this.{$this->getId()}_pages;
                	if (pages.start === 0) {
                        sap.ui.getCore().byId("{$this->getId()}_prev").setEnabled(false);
                	} else {
                        sap.ui.getCore().byId("{$this->getId()}_prev").setEnabled(true);
                	}
                	if (pages.end() === (pages.total - 1)) {
                        sap.ui.getCore().byId("{$this->getId()}_next").setEnabled(false);
                	} else {
                		sap.ui.getCore().byId("{$this->getId()}_next").setEnabled(true);
                	}
                    sap.ui.getCore().byId("{$this->getId()}_pager").setText((pages.start + 1) + ' - ' + (pages.end() + 1) + ' / ' + pages.total);

JS;
    }

    /**
     * Returns the constructor for the table's main toolbar (OverflowToolbar).
     * 
     * The toolbar contains the paginator, all the action buttons, the quick search
     * and the button for the personalization dialog as well as the P13nDialog itself.
     * 
     * The P13nDialog is appended to the toolbar wrapped in an invisible container in
     * order not to affect the overflow behavior. The dialog must be included in the
     * toolbar to ensure it is destroyed with the toolbar and does not become an
     * orphan (e.g. when the view containing the table is destroyed).
     * 
     * @return string
     */
    protected function buildJsToolbar($oControllerJsVar = 'oController')
    {
        $controller = $this->getController();
        $heading = $this->buildTextTableHeading();
        if ($this->getWidget()->getPaginate()) {
            $heading .= ': ';
            $pager = <<<JS
        new sap.m.Label("{$this->getId()}_pager", {
            text: ""
        }),
        new sap.m.OverflowToolbarButton("{$this->getId()}_prev", {
            icon: "sap-icon://navigation-left-arrow",
            layoutData: new sap.m.OverflowToolbarLayoutData({priority: "Low"}),
            text: "{$this->translate('WIDGET.PAGINATOR.PREVIOUS_PAGE')}",
            enabled: false,
            press: function() {
                {$oControllerJsVar}.{$this->getId()}_pages.previous();
                {$this->buildJsRefresh(true, false, $oControllerJsVar)}
            }
        }),
        new sap.m.OverflowToolbarButton("{$this->getId()}_next", {
            icon: "sap-icon://navigation-right-arrow",
            layoutData: new sap.m.OverflowToolbarLayoutData({priority: "Low"}),
            text: "{$this->translate('WIDGET.PAGINATOR.NEXT_PAGE')}",
			enabled: false,
            press: function() {
                {$oControllerJsVar}.{$this->getId()}_pages.next();
                {$this->buildJsRefresh(true, false, $oControllerJsVar)}
            }
        }),
        
JS;
        } else {
            $pager = '';
        }
        $heading = $this->isWrappedInDynamicPage() ? '' : 'new sap.m.Label({text: "' . $heading . '"}),';
        
        $toolbar = <<<JS
			new sap.m.OverflowToolbar({
                design: "Transparent",
				content: [
					{$heading}
			        {$pager}
                    new sap.m.ToolbarSpacer(),
                    {$this->buildJsButtonsConstructors()}
					new sap.m.SearchField("{$this->getId()}_quickSearch", {
                        width: "200px",
                        search: {$controller->buildJsMethodCallFromView('onLoadData', $this)},
                        placeholder: "{$this->getQuickSearchPlaceholder(false)}",
                        layoutData: new sap.m.OverflowToolbarLayoutData({priority: "NeverOverflow"})
                    }),
                    new sap.m.OverflowToolbarButton({
                        icon: "sap-icon://drop-down-list",
                        text: "{$this->translate('WIDGET.DATATABLE.SETTINGS_DIALOG.TITLE')}",
                        tooltip: "{$this->translate('WIDGET.DATATABLE.SETTINGS_DIALOG.TITLE')}",
                        layoutData: new sap.m.OverflowToolbarLayoutData({priority: "High"}),
                        press: function() {
                			{$controller->buildJsDependentControlSelector('oConfigurator', $this, $oControllerJsVar)}.open();
                		}
                    })		
				]
			})
JS;
        return $toolbar;
    }
    
    /**
     * Returns the text to be shown a table title
     * 
     * @return string
     */
    protected function buildTextTableHeading()
    {
        $widget = $this->getWidget();
        return $widget->getCaption() ? $widget->getCaption() : $widget->getMetaObject()->getName();
    }
    
    /**
     * Returns inline JS code to refresh the table.
     * 
     * If the code snippet is to be used somewhere, where the controller is directly accessible, you can pass the
     * name of the controller variable to $oControllerJsVar to increase performance.
     * 
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::buildJsRefresh()
     * 
     * @param bool $keep_page_pos
     * @param bool $growing
     * @param string $oControllerJsVar
     * 
     * @return ui5DataTable
     */
    public function buildJsRefresh($keep_page_pos = false, $growing = false, string $oControllerJsVar = null)
    {
        $params = "undefined, " . ($keep_page_pos ? 'true' : 'false') . ', ' . ($growing ? 'true' : 'false');
        if ($oControllerJsVar === null) {
            return $this->getController()->buildJsMethodCallFromController('onLoadData', $this, $params);
        } else {
            return $this->getController()->buildJsMethodCallFromController('onLoadData', $this, $params, $oControllerJsVar);
        }
    }
    
    /**
     * Returns a ready-to-use comma separated list of javascript constructors for all buttons of the table.
     * 
     * @return string
     */
    protected function buildJsButtonsConstructors()
    {
        $widget = $this->getWidget();
        $buttons = '';
        foreach ($widget->getToolbars() as $toolbar) {
            if ($toolbar->getIncludeSearchActions()){
                $search_button_group = $toolbar->getButtonGroupForSearchActions();
            } else {
                $search_button_group = null;
            }
            foreach ($widget->getToolbarMain()->getButtonGroups() as $btn_group) {
                if ($btn_group === $search_button_group){
                    continue;
                }
                $buttons .= ($buttons && $btn_group->getVisibility() > EXF_WIDGET_VISIBILITY_OPTIONAL ? ",\n new sap.m.ToolbarSeparator()" : '');
                foreach ($btn_group->getButtons() as $btn) {
                    $buttons .= $this->getTemplate()->getElement($btn)->buildJsConstructor() . ",\n";
                }
            }
        }
        return $buttons;
    }

    /**
     * Returns the number of records to show on one page.
     * 
     * @return number
     */
    protected function getPaginationPageSize()
    {
        return $this->getWidget()->getPaginatePageSize() ? $this->getWidget()->getPaginatePageSize() : $this->getTemplate()->getConfig()->getOption('WIDGET.DATATABLE.PAGE_SIZE');
    }
    
    /**
     * Wraps the given content in a constructor for the sap.f.DynamicPage used to create the Fiori list report floorplan.
     * 
     * @param string $content
     * @return string
     */
    protected function buildJsPage($content)
    {  
        foreach ($this->getWidget()->getToolbarMain()->getButtonGroupForSearchActions()->getButtons() as $btn) {
            if ($btn->getAction()->isExactly('exface.Core.RefreshWidget')){
                $btn->setHideButtonIcon(true);
                $btn->setHint($btn->getCaption());
                $btn->setCaption($this->translate('WIDGET.DATATABLE.GO_BUTTON_TEXT'));
            }
            $top_buttons .= $this->getTemplate()->getElement($btn)->buildJsConstructor() . ',';
        }
        return <<<JS

        new sap.f.DynamicPage("{$this->getId()}_page", {
            fitContent: true,
            preserveHeaderStateOnScroll: true,
            headerExpanded: true,
            title: new sap.f.DynamicPageTitle({
				expandedHeading: [
					new sap.m.Title({
                        text: "{$this->buildTextTableHeading()}"
                    })
				],
                snappedHeading: [
                    new sap.m.VBox({
                        items: [
        					new sap.m.Title({
                                text: "{$this->buildTextTableHeading()}"
                            }),
                            new sap.m.Text({
                                text: {
                                    path: "/filterDescription",
                                }
                            })
                        ]
                    })
				],
				actions: [
				    {$top_buttons}
				]
            }),

			header: new sap.f.DynamicPageHeader({
                pinnable: true,
				content: [
                    new sap.ui.layout.Grid({
                        defaultSpan: "XL2 L3 M4 S12",
                        content: [
							{$this->getTemplate()->getElement($this->getWidget()->getConfiguratorWidget())->buildJsFilters()}
						]
                    })
				]
			}),

            content: [
                {$content}
            ]
        })
JS;
    }
    
    /**
     * Returns TRUE if the table will be wrapped in a sap.f.DynamicPage to create a Fiori ListReport
     * 
     * @return boolean
     */
    protected function isWrappedInDynamicPage()
    {
        return $this->getWidget()->hasParent() || $this->getWidget()->getHideHeader() ? false : true;
    }
    
    public function buildJsDataGetter(ActionInterface $action = null)
    {
        if ($action === null) {
            $rows = "sap.ui.getCore().byId('{$this->getId()}').getModel().getData().data";
        } elseif ($action instanceof iReadData) {
            // If we are reading, than we need the special data from the configurator
            // widget: filters, sorters, etc.
            return $this->getTemplate()->getElement($this->getWidget()->getConfiguratorWidget())->buildJsDataGetter($action);
        } elseif ($this->isEditable() && $action->implementsInterface('iModifyData')) {
            $rows = "oTable.getModel().getData().data";
        } else {
            if ($this->isUiTable()) {
                $rows = "(oTable.getSelectedIndex() > -1 ? [oTable.getModel().getData().data[oTable.getSelectedIndex()]] : [])";
            } else {
                $rows = "(oTable.getSelectedItem() ? [oTable.getSelectedItem().getBindingContext().getObject()] : [])";
            }
        }
        return <<<JS
    function() {
        var oTable = sap.ui.getCore().byId('{$this->getId()}');
        var rows = {$rows};
        return {
            oId: '{$this->getWidget()->getMetaObject()->getId()}', 
            rows: (rows === undefined ? [] : rows)
        };
    }()
JS;
    }
        
    protected function buildJsClickListeners($oControllerJsVar = 'oController')
    {
        $widget = $this->getWidget();
        
        $js = '';
        $rightclick_script = '';
        		
        // Double click. Currently only supports one double click action - the first one in the list of buttons
        if ($dblclick_button = $widget->getButtonsBoundToMouseAction(EXF_MOUSE_ACTION_DOUBLE_CLICK)[0]) {
            $js .= <<<JS

            .attachBrowserEvent("dblclick", function(oEvent) {
        		{$this->getTemplate()->getElement($dblclick_button)->buildJsClickEventHandlerCall($oControllerJsVar)};
            })
JS;
        }
        
        // Right click. Currently only supports one double click action - the first one in the list of buttons
        if ($rightclick_button = $widget->getButtonsBoundToMouseAction(EXF_MOUSE_ACTION_RIGHT_CLICK)[0]) {
            $rightclick_script = $this->getTemplate()->getElement($rightclick_button)->buildJsClickEventHandlerCall($oControllerJsVar);
        } else {
            $rightclick_script = $this->buildJsContextMenuTrigger();
        }
        
        if ($rightclick_script) {
            $js .= <<<JS
            
            .attachBrowserEvent("contextmenu", function(oEvent) {
                oEvent.preventDefault();
                {$rightclick_script}
        	})

JS;
        }
                
        // Single click. Currently only supports one click action - the first one in the list of buttons
        if ($leftclick_button = $widget->getButtonsBoundToMouseAction(EXF_MOUSE_ACTION_LEFT_CLICK)[0]) {
            if ($this->isUiTable()) {
                $js .= <<<JS
                
            .attachBrowserEvent("click", function(oEvent) {
        		{$this->getTemplate()->getElement($leftclick_button)->buildJsClickEventHandlerCall($oControllerJsVar)}();
            })
JS;
            } else {
                $js .= <<<JS
                
            .attachItemPress(function(oEvent) {
                {$this->getTemplate()->getElement($leftclick_button)->buildJsClickEventHandlerCall($oControllerJsVar)}();
            })
JS;
            }
        }
        
        return $js;
    }
		
    protected function buildJsContextMenuTrigger($eventJsVar = 'oEvent') {
        return <<<JS
        
                var oMenu = {$this->buildJsContextMenu($this->getWidget()->getButtons())};
                var eFocused = $(':focus');
                var eDock = sap.ui.core.Popup.Dock;
                oMenu.open(true, eFocused, eDock.CenterCenter, eDock.CenterBottom,  {$eventJsVar}.target);
          
JS;
    }
	
    /**
     * 
     * @param Button[]
     * @return string
     */
    protected function buildJsContextMenu(array $buttons)
    {
        return <<<JS

                new sap.ui.unified.Menu({
                    items: [
                        {$this->buildJsContextMenuButtons($buttons)}
                    ]
                })
JS;
    }
        
    /**
     *
     * @param Button[] $buttons
     * @return string
     */
    protected function buildJsContextMenuButtons(array $buttons)
    {
        $context_menu_js = '';
        
        $last_parent = null;
        foreach ($buttons as $button) {
            if ($button->isHidden()) {
                continue;
            }
            if ($button->getParent() == $this->getWidget()->getToolbarMain()->getButtonGroupForSearchActions()) {
                continue;
            }
            if (! is_null($last_parent) && $button->getParent() !== $last_parent) {
                $startSection = true;
            }
            $last_parent = $button->getParent();
            
            $context_menu_js .= ($context_menu_js ? ',' : '') . $this->buildJsContextMenuItem($button, $startSection);
        }
        
        return $context_menu_js;
    }
    
    /**
     * 
     * @param Button $button
     * @param boolean $startSection
     * @return string
     */
    protected function buildJsContextMenuItem(Button $button, $startSection = false)
    {
        $menu_item = '';
        
        $startsSectionProperty = $startSection ? 'startsSection: true,' : '';
        
        /* @var $btn_element \exface\OpenUI5template\Templates\Elements\ui5Button */
        $btn_element = $this->getTemplate()->getElement($button);
        
        if ($button instanceof MenuButton){
            if ($button->getParent() instanceof ButtonGroup && $button === $this->getTemplate()->getElement($button->getParent())->getMoreButtonsMenu()){
                $caption = $button->getCaption() ? $button->getCaption() : '...';
            } else {
                $caption = $button->getCaption();
            }
            $menu_item = <<<JS

                        new sap.ui.unified.MenuItem({
                            icon: "{$btn_element->buildCssIconClass($button->getIcon())}",
                            text: "{$caption}",
                            {$startsSectionProperty}
                            submenu: {$this->buildJsContextMenu($button->getButtons())}
                        })
JS;
        } else {
            $handler = $btn_element->buildJsClickViewEventHandlerCall();
            $select = $handler !== '' ? 'select: ' . $handler . ',' : '';
            $menu_item = <<<JS

                        new sap.ui.unified.MenuItem({
                            icon: "{$btn_element->buildCssIconClass($button->getIcon())}",
                            text: "{$button->getCaption()}",
                            {$select}
                            {$startsSectionProperty}
                        })
JS;
        }
        return $menu_item;
    }
    
    /**
     * 
     * @return ui5DataConfigurator
     */
    protected function getP13nElement()
    {
        return $this->getTemplate()->getElement($this->getWidget()->getConfiguratorWidget());
    }
}
?>