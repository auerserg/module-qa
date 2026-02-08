define([
    'jquery',
    'mage/url',
    'mage/translate'
], function ($, urlBuilder, $t) {
    'use strict';

    urlBuilder.setBaseUrl(window.BASE_URL);

    $.widget('mage.QACommand', {
        options:       {
            messages: '.admin__field-messages .messages',
            command:  'select[name]',
            response: '.response-block pre code',
            refresh:  2000
        },
        _create:       function () {
            this.element.on('submit', $.proxy(this.submit, this));
            this.element.on('change', this.options.command, $.proxy(this.selectCommand, this));
            this.selectCommand();
        },
        selectCommand: function () {
            const $select = this.element.find(this.options.command),
                value = $select.val(),
                $opt = $select.find('option[value="' + value + '"]'),
                id = $opt.data('id');
            this.element.find('[name="command_id"]').val(id);
            if (id) {
                this.logger(id);
            } else {
                $(this.options.messages).html('');
                $(this.options.response).text('');
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
            $.ajax({
                url:        urlBuilder.build('commands/run'),
                type:       'POST',
                dataType:   'json',
                data:       this.element.serialize(),
                showLoader: true,
                success:    function (res) {
                    if (res.message) {
                        self.message(res.message, res.status)
                    }
                    if (res.status === 'info') {
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
            $.ajax({
                url:      urlBuilder.build('commands/log'),
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
                        if ($(self.options.response).text().trim() != res.log) {
                            $(self.options.response).text(res.log);
                            if (Prism) {
                                Prism.highlightAll();
                            }
                        }
                        if (res.isRunning) {
                            setTimeout(() => self.logger(id), self.options.refresh);
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
