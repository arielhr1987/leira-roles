/**
 * This file is used on the term overview page to power quick-editing terms.
 */

/* global inlineEditL10n, ajaxurl, inlineEditUserCapabilities */

window.wp = window.wp || {};
inlineEditL10n = {
    error: "Error while saving the changes.",
    saved: "Changes saved."
};
/**
 * Consists of functions relevant to the inline role editor.
 *
 * @namespace inlineEditUserCapabilities
 *
 * @property {string} type The type of inline edit we are currently on.
 * @property {string} what The type property with a hash prefixed and a dash
 *                         suffixed.
 */
(function ($, wp) {

    window.inlineEditUserCapabilities = {

        /**
         * Initializes the inline role editor by adding event handlers to be able to
         * quick edit.
         *
         * @since 2.7.0
         *
         * @this inlineEditUserCapabilities
         * @memberof inlineEditUserCapabilities
         * @returns {void}
         */
        init: function () {

            var t = this, row = $('#inline-edit-capabilities');

            t.type = $('#the-list').attr('data-wp-lists').substr(5);
            t.what = '#' + t.type + '-';

            $('#the-list').on('click', '.editinline_capabilities', function () {
                $(this).attr('aria-expanded', 'true');
                inlineEditUserCapabilities.edit(this);
            });

            /**
             * Cancels inline editing when pressing escape inside the inline editor.
             *
             * @param {Object} e The keyup event that has been triggered.
             */
            row.keyup(function (e) {
                // 27 = [escape]
                if (e.which === 27) {
                    return inlineEditUserCapabilities.revert();
                }
            });

            /**
             * Cancels inline editing when clicking the cancel button.
             */
            $('.cancel', row).click(function () {
                return inlineEditUserCapabilities.revert();
            });

            /**
             * Saves the inline edits when clicking the save button.
             */
            $('.save', row).click(function () {
                return inlineEditUserCapabilities.save(this);
            });

            /**
             * Saves the inline edits when pressing enter inside the inline editor.
             */
            $('input, select', row).keydown(function (e) {
                // 13 = [enter]
                if (e.which === 13 && $(this).prop('name') !== 'capabilities_search_input') {
                    return inlineEditUserCapabilities.save(this);
                }
            });

            /**
             * Saves the inline edits on submitting the inline edit form.
             */
            $('#posts-filter input[type="submit"]').mousedown(function () {
                t.revert();
            });

            /**
             * Handle check all capabilities
             */
            $(':checkbox.cb-capabilities-select-all', row).on('change', function () {
                var cb = $(this);
                var cbs = cb.parents('.input-text-wrap').find('.capabilities-container :checkbox').not(cb);
                cbs.prop('checked', cb.is(':checked'));
            });

            /**
             * Events to search for capabilities in quick edit mode
             */
            $('input[name="capabilities_search_input"]', row).on('keyup input', function (e) {
                var input = $(this);
                var search = input.val();
                var container = input.parents('.input-text-wrap').find('.capabilities-container');
                //remove not found image
                $('.notice', container).addClass('hidden');
                container.find('> label.capability-item').each(function (index, item) {
                    var toggle = true;
                    if (search.trim() === '') {
                        toggle = true;
                    } else {
                        toggle = $(item).text().indexOf(search) !== -1;
                    }
                    $(this).toggleClass('hidden', !toggle);
                });

                if (!$('> label.capability-item:not(.hidden)', container).length) {
                    //show not found text
                    $('.notice', container).removeClass('hidden');
                }


            }).on('keydown', function (e) {
                // 13 = [enter]
                if (e.which === 13) {
                    return false;
                }
            });
        },

        /**
         * Toggles the quick edit based on if it is currently shown or hidden.
         *
         * @since 2.7.0
         *
         * @this inlineEditUserCapabilities
         * @memberof inlineEditUserCapabilities
         *
         * @param {HTMLElement} el An element within the table row or the table row
         *                         itself that we want to quick edit.
         * @returns {void}
         */
        toggle: function (el) {
            var t = this;

            $(t.what + t.getId(el)).css('display') === 'none' ? t.revert() : t.edit(el);
        },

        /**
         * Shows the quick editor
         *
         * @since 2.7.0
         *
         * @this inlineEditUserCapabilities
         * @memberof inlineEditUserCapabilities
         *
         * @param {string|HTMLElement} id The ID of the term we want to quick edit or an
         *                                element within the table row or the
         * table row itself.
         * @returns {boolean} Always returns false.
         */
        edit: function (id) {
            var editRow, rowData, val,
                t = this;
            t.revert();

            // Makes sure we can pass an HTMLElement as the ID.
            if (typeof (id) === 'object') {
                id = t.getId(id);
            }

            editRow = $('#inline-edit-capabilities').clone(true), rowData = $('#inline_capabilities_' + id);
            $('td', editRow).attr('colspan', $('th:visible, td:visible', '.wp-list-table.widefat:first thead').length);

            $(t.what + id).hide().after(editRow).after('<tr class="hidden"></tr>');

            $('> div', rowData).each(function (index, value) {
                value = $(value);
                var name = value.attr('class');
                value = value.text();

                if (name === 'capabilities') {
                    //show checkboxes for capabilities
                    var container = $('div.capabilities-container', editRow);
                    var json = JSON.parse(value);

                    $.each(json, function (capability, value) {
                        //var title = leira_roles_i18n[capability] ? leira_roles_i18n[capability] : '';
                        var html = '<label class="alignleft capability-item" title="' + capability + '">' +
                            '<input type="checkbox" name="capability[]" value="' + capability + '"' + (value ? ' checked="checked"' : '') + '>' +
                            '<span class="checkbox-title">' + capability + '</span>' +
                            '</label>';
                        container.append(html);
                    });

                } else {
                    $(':input[name=' + name + ']', editRow).val(value);
                }
            });

            $(editRow).attr('id', 'edit-' + id).addClass('inline-editor').show();
            $('.ptitle', editRow).eq(0).focus();

            return false;
        },

        /**
         * Saves the quick edit data.
         *
         * Saves the quick edit data to the server and replaces the table row with the
         * HTML retrieved from the server.
         *
         * @since 2.7.0
         *
         * @this inlineEditUserCapabilities
         * @memberof inlineEditUserCapabilities
         *
         * @param {string|HTMLElement} id The ID of the term we want to quick edit or an
         *                                element within the table row or the
         * table row itself.
         * @returns {boolean} Always returns false.
         */
        save: function (id) {
            var params, fields;

            // Makes sure we can pass an HTMLElement as the ID.
            if (typeof (id) === 'object') {
                id = this.getId(id);
            }

            $('table.widefat .spinner').addClass('is-active');

            params = {
                action: 'leira-roles-quick-edit-user-capabilities',
            };

            fields = $('#edit-' + id).find(':input').serialize();
            params = fields + '&' + $.param(params);

            // Do the ajax request to save the data to the server.
            $.post(ajaxurl, params,
                /**
                 * Handles the response from the server
                 *
                 * Handles the response from the server, replaces the table row with the response
                 * from the server.
                 *
                 * @param {string} r The string with which to replace the table row.
                 */
                function (r) {
                    var row, new_id, option_value,
                        $errorNotice = $('#edit-' + id + ' .inline-edit-save .notice-error'),
                        $error = $errorNotice.find('.error');

                    $('table.widefat .spinner').removeClass('is-active');

                    if (r) {
                        if (-1 !== r.indexOf('<tr')) {
                            $(inlineEditUserCapabilities.what + id).siblings('tr.hidden').addBack().remove();
                            new_id = $(r).attr('id');

                            $('#edit-' + id).before(r).remove();

                            if (new_id) {
                                option_value = new_id.replace(inlineEditUserCapabilities.type + '-', '');
                                row = $('#' + new_id);
                            } else {
                                option_value = id;
                                row = $(inlineEditUserCapabilities.what + id);
                            }

                            row.hide().fadeIn(400, function () {
                                // Move focus back to the Quick Edit button.
                                row.find('.editinline_capabilities')
                                    .attr('aria-expanded', 'false')
                                    .focus();
                                wp.a11y.speak(inlineEditL10n.saved);
                            });

                        } else {
                            $errorNotice.removeClass('hidden');
                            $error.html(r);
                            /*
                             * Some error strings may contain HTML entities (e.g. `&#8220`), let's use
                             * the HTML element's text.
                             */
                            wp.a11y.speak($error.text());
                        }
                    } else {
                        $errorNotice.removeClass('hidden');
                        $error.html(inlineEditL10n.error);
                        wp.a11y.speak(inlineEditL10n.error);
                    }
                }
            );

            // Prevent submitting the form when pressing Enter on a focused field.
            return false;
        },

        /**
         * Closes the quick edit form.
         *
         * @since 2.7.0
         *
         * @this inlineEditUserCapabilities
         * @memberof inlineEditUserCapabilities
         * @returns {void}
         */
        revert: function () {
            var id = $('table.widefat tr.inline-editor').attr('id');

            if (id) {
                $('table.widefat .spinner').removeClass('is-active');
                $('#' + id).siblings('tr.hidden').addBack().remove();
                id = id.substr(id.lastIndexOf('-') + 1);

                // Show the role row and move focus back to the Quick Edit button.
                $(this.what + id).show().find('.editinline_capabilities')
                    .attr('aria-expanded', 'false')
                    .focus();
            }
        },

        /**
         * Retrieves the ID of the term of the element inside the table row.
         *
         * @since 2.7.0
         *
         * @memberof inlineEditUserCapabilities
         *
         * @param {HTMLElement} o An element within the table row or the table row itself.
         * @returns {string} The ID of the term based on the element.
         */
        getId: function (o) {
            var id = o.tagName === 'TR' ? o.id : $(o).parents('tr').attr('id'), parts = id.split('-');

            return parts[parts.length - 1];
        },
    };

    $(document).ready(function () {
        inlineEditUserCapabilities.init();
    });

})(jQuery, window.wp);
