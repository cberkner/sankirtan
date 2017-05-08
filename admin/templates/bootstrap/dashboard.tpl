{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='login'}
{config_load file="$lang.conf" section='shopupdate'}

{if permission('DASHBOARD_VIEW')}
    <script type="text/javascript" src="../includes/libs/flashchart/js/json/json2.js"></script>
    <script type="text/javascript" src="../includes/libs/flashchart/js/swfobject.js"></script>
    <script type="text/javascript" src="{$currentTemplateDir}js/html.sortable.js"></script>
    <script type="text/javascript" src="{$currentTemplateDir}js/dashboard.js"></script>
    <script type="text/javascript">

    // xajax_getAvailableWidgetsAjax();

    function registerWidgetSettings() {ldelim}
        $('[data-widget="add"]').click(function() {ldelim}
            var kWidget = $(this).data('id'),
                myCallback = xajax.callback.create();
            myCallback.onComplete = function(obj) {ldelim}
                window.location.href='index.php?kWidget=' + kWidget;
            {rdelim};
            xajax.call('addWidgetAjax', {ldelim} parameters: [kWidget], callback: myCallback, context: this {rdelim} );
        {rdelim});
    {rdelim}

    $(function() {ldelim}
        xajax_truncateJtllog();
        registerWidgetSettings();
    {rdelim});
    </script>

    <div id="content" class="nomargin">
        <div class="row">
            {include file='tpl_inc/widget_container.tpl' eContainer='left'}
            {include file='tpl_inc/widget_container.tpl' eContainer='center'}
            {include file='tpl_inc/widget_container.tpl' eContainer='right'}
        </div>
    </div>

    <div id="switcher">
        <div class="switcher" id="dashboard-config">
            {*<a href="#" data-toggle="active" data-target="#dashboard-config" class="btn-toggle"><i class="fa fa-gear"></i></a>*}
            
            <a href="#" class="dropdown-toggle parent btn-toggle" data-toggle="dropdown">
                <i class="fa fa-gear"></i>
            </a>
            <div class="switcher-wrapper">
                <div class="switcher-header">
                    <h2>Widgets</h2>
                </div>
                <div class="switcher-content">
                    <div id="settings">
                        {include file='tpl_inc/widget_selector.tpl' oAvailableWidget_arr=$oAvailableWidget_arr}
                    </div>
                </div>
            </div>
        </div>
    </div>
{else}
    {include file='tpl_inc/seite_header.tpl' cTitel=#dashboard#}
    <div class="alert alert-success">
        <strong>Es stehen keine weiteren Informationen zur Verf&uuml;gung.</strong>
    </div>
{/if}

{include file='tpl_inc/footer.tpl'}
