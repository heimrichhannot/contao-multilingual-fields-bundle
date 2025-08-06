/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/scss/contao-multilingual-fields-bundle.be.scss":
/*!***************************************************************!*\
  !*** ./assets/scss/contao-multilingual-fields-bundle.be.scss ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


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
/*!********************************************************!*\
  !*** ./assets/js/contao-multilingual-fields-bundle.js ***!
  \********************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _scss_contao_multilingual_fields_bundle_be_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../scss/contao-multilingual-fields-bundle.be.scss */ "./assets/scss/contao-multilingual-fields-bundle.be.scss");

function moveEditButton() {
  var widget = document.getElementById('mf_language_edit_switch_button_widget');
  var element = widget.getElementsByTagName('a')[0];
  var buttons = document.getElementById('tl_buttons');
  var elemParent = widget.parentElement;
  if (element && buttons) {
    buttons.appendChild(element);
    if (elemParent) {
      elemParent.remove();
    }
  }
}
document.addEventListener('DOMContentLoaded', moveEditButton);
})();

/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY29udGFvLW11bHRpbGluZ3VhbC1maWVsZHMtYnVuZGxlLmpzIiwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7O0FBQUE7Ozs7Ozs7VUNBQTtVQUNBOztVQUVBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBOztVQUVBO1VBQ0E7O1VBRUE7VUFDQTtVQUNBOzs7OztXQ3RCQTtXQUNBO1dBQ0E7V0FDQSx1REFBdUQsaUJBQWlCO1dBQ3hFO1dBQ0EsZ0RBQWdELGFBQWE7V0FDN0QsRTs7Ozs7Ozs7Ozs7O0FDTjJEO0FBRTNELFNBQVNBLGNBQWNBLENBQUEsRUFBRztFQUN0QixJQUFJQyxNQUFNLEdBQUdDLFFBQVEsQ0FBQ0MsY0FBYyxDQUFDLHVDQUF1QyxDQUFDO0VBQzdFLElBQUlDLE9BQU8sR0FBR0gsTUFBTSxDQUFDSSxvQkFBb0IsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUM7RUFDakQsSUFBSUMsT0FBTyxHQUFHSixRQUFRLENBQUNDLGNBQWMsQ0FBQyxZQUFZLENBQUM7RUFDbkQsSUFBSUksVUFBVSxHQUFHTixNQUFNLENBQUNPLGFBQWE7RUFFckMsSUFBSUosT0FBTyxJQUFJRSxPQUFPLEVBQUU7SUFDcEJBLE9BQU8sQ0FBQ0csV0FBVyxDQUFDTCxPQUFPLENBQUM7SUFDNUIsSUFBSUcsVUFBVSxFQUFFO01BQ1pBLFVBQVUsQ0FBQ0csTUFBTSxDQUFDLENBQUM7SUFDdkI7RUFDSjtBQUNKO0FBRUFSLFFBQVEsQ0FBQ1MsZ0JBQWdCLENBQUMsa0JBQWtCLEVBQUVYLGNBQWMsQ0FBQyxDIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vY29udGFvLW11bHRpbGluZ3VhbC1maWVsZHMtYnVuZGxlLy4vYXNzZXRzL3Njc3MvY29udGFvLW11bHRpbGluZ3VhbC1maWVsZHMtYnVuZGxlLmJlLnNjc3M/ZDEyZSIsIndlYnBhY2s6Ly9jb250YW8tbXVsdGlsaW5ndWFsLWZpZWxkcy1idW5kbGUvd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vY29udGFvLW11bHRpbGluZ3VhbC1maWVsZHMtYnVuZGxlL3dlYnBhY2svcnVudGltZS9tYWtlIG5hbWVzcGFjZSBvYmplY3QiLCJ3ZWJwYWNrOi8vY29udGFvLW11bHRpbGluZ3VhbC1maWVsZHMtYnVuZGxlLy4vYXNzZXRzL2pzL2NvbnRhby1tdWx0aWxpbmd1YWwtZmllbGRzLWJ1bmRsZS5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyIvLyBleHRyYWN0ZWQgYnkgbWluaS1jc3MtZXh0cmFjdC1wbHVnaW5cbmV4cG9ydCB7fTsiLCIvLyBUaGUgbW9kdWxlIGNhY2hlXG52YXIgX193ZWJwYWNrX21vZHVsZV9jYWNoZV9fID0ge307XG5cbi8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG5mdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cdC8vIENoZWNrIGlmIG1vZHVsZSBpcyBpbiBjYWNoZVxuXHR2YXIgY2FjaGVkTW9kdWxlID0gX193ZWJwYWNrX21vZHVsZV9jYWNoZV9fW21vZHVsZUlkXTtcblx0aWYgKGNhY2hlZE1vZHVsZSAhPT0gdW5kZWZpbmVkKSB7XG5cdFx0cmV0dXJuIGNhY2hlZE1vZHVsZS5leHBvcnRzO1xuXHR9XG5cdC8vIENyZWF0ZSBhIG5ldyBtb2R1bGUgKGFuZCBwdXQgaXQgaW50byB0aGUgY2FjaGUpXG5cdHZhciBtb2R1bGUgPSBfX3dlYnBhY2tfbW9kdWxlX2NhY2hlX19bbW9kdWxlSWRdID0ge1xuXHRcdC8vIG5vIG1vZHVsZS5pZCBuZWVkZWRcblx0XHQvLyBubyBtb2R1bGUubG9hZGVkIG5lZWRlZFxuXHRcdGV4cG9ydHM6IHt9XG5cdH07XG5cblx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG5cdF9fd2VicGFja19tb2R1bGVzX19bbW9kdWxlSWRdKG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG5cdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG5cdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbn1cblxuIiwiLy8gZGVmaW5lIF9fZXNNb2R1bGUgb24gZXhwb3J0c1xuX193ZWJwYWNrX3JlcXVpcmVfXy5yID0gKGV4cG9ydHMpID0+IHtcblx0aWYodHlwZW9mIFN5bWJvbCAhPT0gJ3VuZGVmaW5lZCcgJiYgU3ltYm9sLnRvU3RyaW5nVGFnKSB7XG5cdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIFN5bWJvbC50b1N0cmluZ1RhZywgeyB2YWx1ZTogJ01vZHVsZScgfSk7XG5cdH1cblx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsICdfX2VzTW9kdWxlJywgeyB2YWx1ZTogdHJ1ZSB9KTtcbn07IiwiaW1wb3J0ICcuLi9zY3NzL2NvbnRhby1tdWx0aWxpbmd1YWwtZmllbGRzLWJ1bmRsZS5iZS5zY3NzJztcblxuZnVuY3Rpb24gbW92ZUVkaXRCdXR0b24oKSB7XG4gICAgbGV0IHdpZGdldCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdtZl9sYW5ndWFnZV9lZGl0X3N3aXRjaF9idXR0b25fd2lkZ2V0Jyk7XG4gICAgbGV0IGVsZW1lbnQgPSB3aWRnZXQuZ2V0RWxlbWVudHNCeVRhZ05hbWUoJ2EnKVswXTtcbiAgICBsZXQgYnV0dG9ucyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCd0bF9idXR0b25zJyk7XG4gICAgbGV0IGVsZW1QYXJlbnQgPSB3aWRnZXQucGFyZW50RWxlbWVudDtcblxuICAgIGlmIChlbGVtZW50ICYmIGJ1dHRvbnMpIHtcbiAgICAgICAgYnV0dG9ucy5hcHBlbmRDaGlsZChlbGVtZW50KTtcbiAgICAgICAgaWYgKGVsZW1QYXJlbnQpIHtcbiAgICAgICAgICAgIGVsZW1QYXJlbnQucmVtb3ZlKCk7XG4gICAgICAgIH1cbiAgICB9XG59XG5cbmRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ0RPTUNvbnRlbnRMb2FkZWQnLCBtb3ZlRWRpdEJ1dHRvbik7Il0sIm5hbWVzIjpbIm1vdmVFZGl0QnV0dG9uIiwid2lkZ2V0IiwiZG9jdW1lbnQiLCJnZXRFbGVtZW50QnlJZCIsImVsZW1lbnQiLCJnZXRFbGVtZW50c0J5VGFnTmFtZSIsImJ1dHRvbnMiLCJlbGVtUGFyZW50IiwicGFyZW50RWxlbWVudCIsImFwcGVuZENoaWxkIiwicmVtb3ZlIiwiYWRkRXZlbnRMaXN0ZW5lciJdLCJzb3VyY2VSb290IjoiIn0=