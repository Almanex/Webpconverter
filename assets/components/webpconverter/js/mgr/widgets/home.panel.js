WebpConverter.page.Home = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'webpconverter-panel-home',
            renderTo: 'webpconverter-panel-home-div'
        }]
    });
    WebpConverter.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(WebpConverter.page.Home, MODx.Component);
Ext.reg('webpconverter-page-home', WebpConverter.page.Home);

WebpConverter.panel.Home = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        cls: 'container',
        defaults: { collapsible: false, autoHeight: true },
        items: [{
            html: '<h2>' + _('webpconverter.title') + '</h2>',
            border: false,
            cls: 'modx-page-header'
        }, {
            xtype: 'modx-panel',
            cls: 'structure',
            items: [{
                html: '<p>' + _('webpconverter.desc') + '</p>',
                border: false,
                bodyCssClass: 'panel-desc'
            }, {
                cls: 'main-wrapper',
                style: 'padding: 20px;',
                border: false,
                layout: 'form',
                items: [{
                    layout: 'column',
                    border: false,
                    items: [{
                        columnWidth: .5,
                        layout: 'form',
                        border: false,
                        items: [{
                            xtype: 'button',
                            text: _('webpconverter.scan'),
                            cls: 'primary-button',
                            handler: this.startScan,
                            scope: this,
                            anchor: '100%',
                            scale: 'large'
                        }]
                    }, {
                        columnWidth: .5,
                        layout: 'form',
                        border: false,
                        style: 'margin-left: 20px;',
                        items: [{
                            xtype: 'button',
                            text: _('webpconverter.clean'),
                            handler: this.startClean,
                            scope: this,
                            anchor: '100%',
                            scale: 'large'
                        }]
                    }]
                }, {
                    id: 'webpconverter-progress-wrapper',
                    style: 'margin-top: 30px; display: none;',
                    border: false,
                    items: [{
                        xtype: 'progress',
                        id: 'webpconverter-progress-bar',
                        text: _('webpconverter.processing'),
                        width: '100%'
                    }]
                }, {
                    id: 'webpconverter-status-panel',
                    html: '<div id="webpconverter-status-box" style="margin-top: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; background: #fafafa; font-family: monospace; max-height: 300px; overflow-y: auto;">' + _('webpconverter.queue_empty') + '</div>',
                    border: false
                }]
            }]
        }]
    });
    WebpConverter.panel.Home.superclass.constructor.call(this, config);
    
    // Internal queue variables
    this.queue = [];
    this.totalCount = 0;
    this.processedCount = 0;
    this.cwebp = '';
    this.activeThreads = 0;
    this.maxThreads = 3;
};
Ext.extend(WebpConverter.panel.Home, MODx.Panel, {
    
    log: function(message, type) {
        const box = document.getElementById('webpconverter-status-box');
        if (!box) return;
        
        let color = '#333';
        if (type === 'success') color = '#2e7d32';
        if (type === 'error') color = '#c62828';
        if (type === 'info') color = '#1565c0';
        
        const time = new Date().toLocaleTimeString();
        box.innerHTML += `<div style="color: ${color}; margin-bottom: 4px;">[${time}] ${message}</div>`;
        box.scrollTop = box.scrollHeight;
    },

    clearLog: function() {
        const box = document.getElementById('webpconverter-status-box');
        if (box) box.innerHTML = '';
    },

    startScan: function() {
        if (this.activeThreads > 0) return;
        
        this.clearLog();
        this.log('Запуск сканирования файлов на сервере... Пожалуйста, подождите (это может занять до 1-2 минут на больших сайтах).', 'info');
        
        const progressWrapper = Ext.getCmp('webpconverter-progress-wrapper');
        const progressBar = Ext.getCmp('webpconverter-progress-bar');
        progressWrapper.show();
        progressBar.updateProgress(0, 'Поиск и сканирование файлов...');

        // Start fake progress timer to show activity
        if (this.scanTimer) clearInterval(this.scanTimer);
        let progress = 0;
        this.scanTimer = setInterval(() => {
            progress += 0.05;
            if (progress >= 0.95) {
                progress = 0.95;
                clearInterval(this.scanTimer);
            }
            progressBar.updateProgress(progress, 'Поиск и сканирование файлов на сервере...');
        }, 1500);

        MODx.Ajax.request({
            url: WebpConverter.config.connector_url,
            params: {
                action: 'scan'
            },
            listeners: {
                success: {
                    fn: function(r) {
                        if (this.scanTimer) clearInterval(this.scanTimer);
                        if (r.results && r.results.images) {
                            this.queue = r.results.images;
                            this.totalCount = r.results.count;
                            this.processedCount = 0;
                            this.cwebp = r.results.cwebp;
                            
                            this.log(_('webpconverter.info.total') + '<strong>' + this.totalCount + '</strong>', 'info');
                            this.log(_('webpconverter.info.cwebp') + '<strong>' + this.cwebp + '</strong>', 'info');

                            if (this.totalCount > 0) {
                                this.log('Начало оптимизации картинок...', 'info');
                                this.startConversionLoop();
                            } else {
                                progressBar.updateProgress(1, _('webpconverter.complete'));
                                this.log(_('webpconverter.complete'), 'success');
                            }
                        }
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        if (this.scanTimer) clearInterval(this.scanTimer);
                        progressBar.updateProgress(0, 'Ошибка сканирования');
                        this.log('Ошибка при сканировании: ' + (r.message || 'неизвестная ошибка'), 'error');
                    },
                    scope: this
                }
            }
        });
    },

    startConversionLoop: function() {
        this.activeThreads = 0;
        const threadsToStart = Math.min(this.maxThreads, this.queue.length);
        
        for (let i = 0; i < threadsToStart; i++) {
            this.activeThreads++;
            this.processNext();
        }
    },

    processNext: function() {
        if (this.queue.length === 0) {
            this.activeThreads--;
            if (this.activeThreads === 0) {
                this.finishBatch();
            }
            return;
        }

        const file = this.queue.shift();
        
        MODx.Ajax.request({
            url: WebpConverter.config.connector_url,
            params: {
                action: 'convert',
                file: file,
                cwebp: this.cwebp
            },
            listeners: {
                success: {
                    fn: function(r) {
                        this.processedCount++;
                        this.log('Успешно сжат: ' + file, 'success');
                        this.updateProgress();
                        this.processNext();
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        this.processedCount++;
                        this.log('Ошибка сжатия: ' + file + ' (' + (r.message || '') + ')', 'error');
                        this.updateProgress();
                        this.processNext();
                    },
                    scope: this
                }
            }
        });
    },

    updateProgress: function() {
        const progressBar = Ext.getCmp('webpconverter-progress-bar');
        const ratio = this.processedCount / this.totalCount;
        progressBar.updateProgress(ratio, `Оптимизировано: ${this.processedCount} из ${this.totalCount}`);
    },

    finishBatch: function() {
        this.log('Все файлы обработаны. Запуск финальной очистки кэша...', 'info');
        
        MODx.Ajax.request({
            url: WebpConverter.config.connector_url,
            params: {
                action: 'clean'
            },
            listeners: {
                success: {
                    fn: function(r) {
                        this.log(_('webpconverter.complete'), 'success');
                        Ext.getCmp('webpconverter-progress-bar').updateProgress(1, _('webpconverter.complete'));
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        this.log('Ошибка финальной очистки.', 'error');
                    },
                    scope: this
                }
            }
        });
    },

    startClean: function() {
        if (this.activeThreads > 0) return;
        
        Ext.Msg.confirm(
            _('webpconverter.clean'),
            'Вы уверены, что хотите очистить кэш WebP? Это действие удалит все оптимизированные файлы WebP, оригиналы которых были удалены с сервера, чтобы освободить место на диске. Рабочие WebP-изображения затронуты не будут.',
            function(e) {
                if (e === 'yes') {
                    this.clearLog();
                    this.log('Запуск очистки устаревших файлов WebP...', 'info');

                    MODx.Ajax.request({
                        url: WebpConverter.config.connector_url,
                        params: {
                            action: 'clean'
                        },
                        listeners: {
                            success: {
                                fn: function(r) {
                                    this.log('Очистка завершена! Устаревшие файлы удалены с сервера.', 'success');
                                },
                                scope: this
                            },
                            failure: {
                                fn: function(r) {
                                    this.log('Ошибка при очистке.', 'error');
                                },
                                scope: this
                            }
                        }
                    });
                }
            },
            this
        );
    }
});
Ext.reg('webpconverter-panel-home', WebpConverter.panel.Home);
