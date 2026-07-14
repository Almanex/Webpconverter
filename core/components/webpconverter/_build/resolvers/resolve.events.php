<?php
/** @var xPDOTransport $transport */
/** @var array $options */
if ($object->xpdo) {
    /** @var modX $modx */
    $modx =& $object->xpdo;
    
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $plugin = $modx->getObject('MODX\Revolution\modPlugin', ['name' => 'WebpConverter']);
            if ($plugin) {
                $events = [
                    'OnManagerPageBeforeRender',
                    'OnWebPagePrerender',
                    'OnSiteRefresh',
                    'OnTemplateSave',
                    'OnChunkSave',
                    'OnPluginSave',
                    'OnSnippetSave',
                    'OnTemplateVarSave',
                    'OnDocFormSave'
                ];

                foreach ($events as $eventName) {
                    $event = $modx->getObject('MODX\Revolution\modPluginEvent', [
                        'pluginid' => $plugin->get('id'),
                        'event' => $eventName
                    ]);
                    if (!$event) {
                        $event = $modx->newObject('MODX\Revolution\modPluginEvent');
                        $event->set('pluginid', $plugin->get('id'));
                        $event->set('event', $eventName);
                        $event->set('priority', 0);
                        $event->save();
                    }
                }
            }

            // Register Dashboard Widget
            $widget = $modx->getObject('MODX\Revolution\modDashboardWidget', ['name' => 'webpconverter.widget']);
            if (!$widget) {
                $widget = $modx->newObject('MODX\Revolution\modDashboardWidget');
                $widget->set('name', 'webpconverter.widget');
                $widget->set('description', 'webpconverter.widget_desc');
                $widget->set('type', 'file');
                $widget->set('content', '[[++core_path]]components/webpconverter/elements/widgets/webpconverter.widget.php');
                $widget->set('namespace', 'webpconverter');
                $widget->set('lexicon', 'webpconverter:default');
                $widget->set('size', 'half');
                $widget->save();
            } else {
                $widget->set('content', '[[++core_path]]components/webpconverter/elements/widgets/webpconverter.widget.php');
                $widget->save();
            }

            // Place on Default Dashboard (ID 1)
            if ($widget) {
                $placement = $modx->getObject('MODX\Revolution\modDashboardWidgetPlacement', [
                    'widget' => $widget->get('id'),
                    'dashboard' => 1
                ]);
                if (!$placement) {
                    $placement = $modx->newObject('MODX\Revolution\modDashboardWidgetPlacement');
                    $placement->set('widget', $widget->get('id'));
                    $placement->set('dashboard', 1);
                    $placement->set('user', 0);
                    
                    $c = $modx->newQuery('MODX\Revolution\modDashboardWidgetPlacement');
                    $c->where(['dashboard' => 1]);
                    $c->sortby('rank', 'DESC');
                    $c->limit(1);
                    $last = $modx->getObject('MODX\Revolution\modDashboardWidgetPlacement', $c);
                    $rank = $last ? $last->get('rank') + 1 : 0;
                    
                    $placement->set('rank', $rank);
                    $placement->save();
                }
            }
            break;
            
        case xPDOTransport::ACTION_UNINSTALL:
            $widget = $modx->getObject('MODX\Revolution\modDashboardWidget', ['name' => 'webpconverter.widget']);
            if ($widget) {
                $modx->removeCollection('MODX\Revolution\modDashboardWidgetPlacement', ['widget' => $widget->get('id')]);
                $widget->remove();
            }
            break;
    }
}
return true;
