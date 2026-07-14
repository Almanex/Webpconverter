<?php
/**
 * Home manager controller
 *
 * @package webpconverter
 * @subpackage controllers
 */

class WebpConverterHomeManagerController extends modExtraManagerController {
    /** @var \WebpConverter\WebpConverter */
    public $webpconverter;

    public function initialize() {
        $this->webpconverter = $this->modx->services->get('webpconverter');
        if (!$this->webpconverter) {
            // Load if not loaded
            $corePath = $this->modx->getOption('core_path') . 'components/webpconverter/';
            require_once $corePath . 'bootstrap.php';
            $this->webpconverter = $this->modx->services->get('webpconverter');
        }

        $this->addCss($this->webpconverter->options['assetsUrl'] . 'css/mgr.css?v=1.0.3');
        $this->addJavascript($this->webpconverter->options['assetsUrl'] . 'js/mgr/webpconverter.js?v=1.0.3');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            WebpConverter.config = ' . $this->modx->toJSON($this->webpconverter->options) . ';
            WebpConverter.config.connector_url = "' . $this->webpconverter->options['connectorUrl'] . '";
        });
        </script>');
        return parent::initialize();
    }

    public function getLanguageTopics() {
        return array('webpconverter:default');
    }

    public function getPageTitle() {
        return $this->modx->lexicon('webpconverter');
    }

    public function loadCustomCssJs() {
        $this->addJavascript($this->webpconverter->options['assetsUrl'] . 'js/mgr/widgets/home.panel.js?v=1.0.3');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            MODx.load({ xtype: "webpconverter-page-home"});
        });
        </script>');
    }

    public function getTemplateFile() {
        return $this->webpconverter->options['corePath'] . 'templates/home.tpl';
    }
}
