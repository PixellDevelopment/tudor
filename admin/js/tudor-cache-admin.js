/* Tudor Cache Admin – tudor-cache-admin.js */
/* global jQuery, tudorCacheAdmin */

jQuery(function ($) {
  'use strict';

  var $btn     = $('#tudor-clear-cache-btn');
  var $msg     = $('#tudor-cache-message');
  var $version = $('#tudor-cache-version');

  $btn.on('click', function () {
    var originalText = $btn.text();

    $btn.prop('disabled', true).text(tudorCacheAdmin.i18n.clearing);
    $msg.hide().removeClass('notice-success notice-error notice inline');

    $.post(
      tudorCacheAdmin.ajaxUrl,
      {
        action: 'tudor_clear_cache',
        nonce:  tudorCacheAdmin.nonce,
      }
    )
    .done(function (response) {
      if (response.success) {
        $version.text(response.data.version);
        showMessage('success', '✅ ' + tudorCacheAdmin.i18n.cleared + ' (v' + response.data.version + ')');
      } else {
        var errMsg = (response.data && response.data.message) ? response.data.message : tudorCacheAdmin.i18n.error;
        showMessage('error', '❌ ' + errMsg);
      }
    })
    .fail(function (jqXHR) {
      showMessage('error', '❌ ' + tudorCacheAdmin.i18n.error + ' (' + jqXHR.status + ')');
    })
    .always(function () {
      $btn.prop('disabled', false).text(originalText);
    });
  });

  function showMessage(type, text) {
    $msg
      .addClass('notice notice-' + type + ' inline')
      .html('<p>' + text + '</p>')
      .show();
  }
});
