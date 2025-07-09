/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/***/ ((module) => {

module.exports = window["jQuery"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!****************************!*\
  !*** ./src/roles-admin.js ***!
  \****************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "jquery");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/**
 * Contains logic for deleting and adding roles.
 *
 * For deleting roles it makes a request to the server to delete the tag.
 * For adding roles, it makes a request to the server to add the tag.
 *
 */

/* global ajaxurl, wpAjax, tagsl10n, showNotice, validateForm */

jQuery(document).ready(function ($) {
  /**
   * Adds an event handler to the delete role link on the role overview page.
   *
   * Cancels default event handling and event bubbling.
   *
   * @since 2.8.0
   *
   * @returns boolean Always returns false to cancel the default event handling.
   */
  $('#the-list').on('click', '.delete-role', function () {
    var t = $(this),
      tr = t.parents('tr'),
      r = true,
      data;
    data = t.attr('href').replace(/[^?]*\?/, '');

    /**
     * Makes a request to the server to delete the role that corresponds to the delete role button.
     *
     * @param {string} r The response from the server.
     *
     * @returns {void}
     */
    $.post(ajaxurl, data, function (r) {
      if (r) {
        if (r.success === true) {
          $('#ajax-response').empty();
          tr.fadeOut('normal', function () {
            tr.remove();
          });
        } else {
          $('#ajax-response').empty().append(r.data);
          tr.children().css('backgroundColor', '');
        }
      }
    });
    tr.children().css('backgroundColor', '#f33');
    return false;
  });

  /**
   * Adds an event handler to the clone role link on the role overview page.
   *
   * Cancels default event handling and event bubbling.
   *
   * @since 2.8.0
   *
   * @returns boolean Always returns false to cancel the default event handling.
   */
  $('#the-list').on('click', '.clone-role', function () {
    var t = $(this),
      tr = t.parents('tr'),
      r = true,
      data;
    data = t.attr('href').replace(/[^?]*\?/, '');

    /**
     * Makes a request to the server to clone the role that corresponds to the delete role button.
     *
     * @param {string} r The response from the server.
     *
     * @returns {void}
     */
    $.post(ajaxurl, data, function (r) {
      if (r) {
        if (r.success === true) {
          $('#ajax-response').empty();
          tr.after($(r.data));
        } else {
          $('#ajax-response').empty().append(r.data);
          //tr.children().css('backgroundColor', '');
        }
      }
    });
    return false;
  });

  /**
   * Adds a deletion confirmation when removing a role.
   *
   * @since 4.8.0
   *
   * @returns {void}
   */
  $('#edittag').on('click', '.delete', function (e) {
    if ('undefined' === typeof showNotice) {
      return true;
    }

    // Confirms the deletion; a negative response means the deletion must not be executed.
    var response = showNotice.warn();
    if (!response) {
      e.preventDefault();
    }
  });

  /**
   * Adds an event handler to the form submit on the role overview page.
   *
   * Cancels default event handling and event bubbling.
   *
   * @since 2.8.0
   *
   * @returns boolean Always returns false to cancel the default event handling.
   */
  $('#submit').click(function () {
    var form = $(this).parents('form');
    if (!validateForm(form)) return false;

    /**
     * Does a request to the server to add a new role to the system
     *
     * @param {string} r The response from the server.
     *
     * @returns {void}
     */
    $.post(ajaxurl, $('#addrole').serialize(), function (r) {
      var res, parent, role, indent, i;
      $('#ajax-response').empty();
      res = typeof r !== 'undefined' ? r : null;
      if (res) {
        if (res.success === false) {
          $('#ajax-response').append(res.data);
        } else if (res.success === true) {
          $('.roles').prepend(res.data); // add to the table

          $('.roles .no-items').remove();
          $('input[type="text"]:visible, textarea:visible', form).val('');
        }
      }
    });
    return false;
  });
});
})();

/******/ })()
;
//# sourceMappingURL=roles-admin.js.map