define([
    'jquery',
    'mage/url'
], function ($, urlBuilder) {
    'use strict';

    urlBuilder.setBaseUrl(window.BASE_URL);

    $.widget('mage.QACommand', {
        options:       {
            messages: '.admin__field-messages .messages',
            command:  'select[name]',
            response: '.response-block pre code',
            refresh:  2000,
            runUrl:   urlBuilder.build('commands/run'),
            logUrl:   urlBuilder.build('commands/log'),
        },
        _create:       function () {
            this.element.on('submit', $.proxy(this.submit, this));
            this.element.on('change', this.options.command, $.proxy(this.selectCommand, this));
            this.selectCommand();
        },
        selectCommand: function () {
            const $select = this.element.find(this.options.command),
                $opt = $select.find('option:selected'),
                id = $opt.data('id');
            this.element.find('[name="command_id"]').val(id);
            this.log_id = id;
            if (id) {
                this.logger(id);
            } else {
                this.reset();
                $(this.options.messages).html('');
                $(this.options.response).text('');
            }
        },
        reset:         function () {
            if (this.xhr) {
                this.xhr.abort();
            }
            if (this.timeout) {
                clearTimeout(this.timeout);
            }
        },
        message:       function (msg, type = 'success') {
            const $msg = $('<div>').attr('class', [
                'message',
                'message-' + type,
                type
            ].join(' ')).html($('<div>').attr('data-ui-id', 'messages-message-' + type).html(msg));
            $(this.options.messages).html($msg);
        },
        submit:        function (e) {
            e.preventDefault();
            const self = this;
            this.log_id = undefined;
            this.reset();
            this.xhr = $.ajax({
                url:        this.options.runUrl,
                type:       'POST',
                dataType:   'json',
                data:       this.element.serialize(),
                showLoader: true,
                success:    function (res) {
                    if (res.message) {
                        self.message(res.message, res.status)
                    }
                    if (res.status === 'info') {
                        self.log_id = res.processId;
                        self.logger(res.processId);
                    }
                },
                error:      function (err) {
                    self.message(err.responseText || err, 'error')
                }
            });
        },
        logger:        function (id) {
            const self = this;
            if (this.log_id !== id) {
                return;
            }
            this.xhr = $.ajax({
                url:      this.options.logUrl,
                type:     'GET',
                dataType: 'json',
                data:     {
                    id,
                    _: new Date().getTime()
                },
                success:  function (res) {
                    if (res.message) {
                        self.message(res.message, res.status)
                    }
                    if (res.status === 'success') {
                        const $response = $(self.options.response);
                        if (parseInt($response.attr('data-length')) !== res.log.length) {
                            $response.attr('data-length', res.log.length).text(res.log);
                            if (Prism) {
                                Prism.highlightAll();
                            }
                        }
                        if (res.isRunning) {
                            this.timeout = setTimeout(() => self.logger(id), self.options.refresh);
                        }
                    }
                },
                error:    function (err) {
                    self.message(err.responseText || err, 'error')
                }
            });
        }

    });
    return $.mage.QACommand;
});
