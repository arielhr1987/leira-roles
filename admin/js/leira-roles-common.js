/**
 * Contains all common logic to include in all admin plugin pages
 *
 * @since 1.1.3
 */

/* global ajaxurl */

jQuery(document).ready(function ($) {

    /**
     * Handle footer rate us link
     *
     * @since 1.1.3
     */
    $('body').on('click', 'a.leira-roles-admin-rating-link', function () {
        $.post(ajaxurl, {
            action: 'leira-roles-footer-rated',
            _wpnonce: $(this).data('nonce')
        }, function () {
            //on success do nothing
        });
        $(this).parent().text($(this).data('rated'));
    });

});