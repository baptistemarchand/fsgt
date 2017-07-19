/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/build/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/main.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/main.js":
/***/ (function(module, exports) {

var elements = stripe.elements();

// Custom styling can be passed to options when creating an Element.
var style = {
    base: {
        color: '#32325d',
        lineHeight: '24px',
        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
        fontSmoothing: 'antialiased',
        fontSize: '16px',
        '::placeholder': {
            color: '#aab7c4'
        }
    },
    invalid: {
        color: '#fa755a',
        iconColor: '#fa755a'
    }
};

// Create an instance of the card Element
var card = elements.create('card', { style: style });

// Add an instance of the card Element into the `card-element` <div>
card.mount('#card-element');
card.addEventListener('change', function (event) {
    var displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});

// Create a token or display an error when the form is submitted.
var form = document.getElementById('payment-form');
form.addEventListener('submit', function (event) {
    event.preventDefault();

    stripe.createToken(card).then(function (result) {
        if (result.error) {
            // Inform the user if there was an error
            var errorElement = document.getElementById('card-errors');
            errorElement.textContent = result.error.message;
        } else {
            // Send the token to your server
            stripeTokenHandler(result.token);
        }
    });
});

function stripeTokenHandler(token) {
    // Insert the token ID into the form so it gets submitted to the server
    var form = document.getElementById('payment-form');
    var hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'stripeToken');
    hiddenInput.setAttribute('value', token.id);
    form.appendChild(hiddenInput);

    // Submit the form
    form.submit();
}

/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAgYWYzZGYyM2JkYzNiNzAwNWY2ZTUiLCJ3ZWJwYWNrOi8vLy4vYXNzZXRzL2pzL21haW4uanMiXSwibmFtZXMiOlsiZWxlbWVudHMiLCJzdHJpcGUiLCJzdHlsZSIsImJhc2UiLCJjb2xvciIsImxpbmVIZWlnaHQiLCJmb250RmFtaWx5IiwiZm9udFNtb290aGluZyIsImZvbnRTaXplIiwiaW52YWxpZCIsImljb25Db2xvciIsImNhcmQiLCJjcmVhdGUiLCJtb3VudCIsImFkZEV2ZW50TGlzdGVuZXIiLCJldmVudCIsImRpc3BsYXlFcnJvciIsImRvY3VtZW50IiwiZ2V0RWxlbWVudEJ5SWQiLCJlcnJvciIsInRleHRDb250ZW50IiwibWVzc2FnZSIsImZvcm0iLCJwcmV2ZW50RGVmYXVsdCIsImNyZWF0ZVRva2VuIiwidGhlbiIsInJlc3VsdCIsImVycm9yRWxlbWVudCIsInN0cmlwZVRva2VuSGFuZGxlciIsInRva2VuIiwiaGlkZGVuSW5wdXQiLCJjcmVhdGVFbGVtZW50Iiwic2V0QXR0cmlidXRlIiwiaWQiLCJhcHBlbmRDaGlsZCIsInN1Ym1pdCJdLCJtYXBwaW5ncyI6IjtBQUFBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOzs7QUFHQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQSxtREFBMkMsY0FBYzs7QUFFekQ7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxhQUFLO0FBQ0w7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQSxtQ0FBMkIsMEJBQTBCLEVBQUU7QUFDdkQseUNBQWlDLGVBQWU7QUFDaEQ7QUFDQTtBQUNBOztBQUVBO0FBQ0EsOERBQXNELCtEQUErRDs7QUFFckg7QUFDQTs7QUFFQTtBQUNBOzs7Ozs7OztBQ2hFQSxJQUFJQSxXQUFXQyxPQUFPRCxRQUFQLEVBQWY7O0FBRUE7QUFDQSxJQUFJRSxRQUFRO0FBQ1JDLFVBQU07QUFDRkMsZUFBTyxTQURMO0FBRUZDLG9CQUFZLE1BRlY7QUFHRkMsb0JBQVkseUNBSFY7QUFJRkMsdUJBQWUsYUFKYjtBQUtGQyxrQkFBVSxNQUxSO0FBTUYseUJBQWlCO0FBQ2JKLG1CQUFPO0FBRE07QUFOZixLQURFO0FBV1JLLGFBQVM7QUFDTEwsZUFBTyxTQURGO0FBRUxNLG1CQUFXO0FBRk47QUFYRCxDQUFaOztBQWlCQTtBQUNBLElBQUlDLE9BQU9YLFNBQVNZLE1BQVQsQ0FBZ0IsTUFBaEIsRUFBd0IsRUFBQ1YsT0FBT0EsS0FBUixFQUF4QixDQUFYOztBQUVBO0FBQ0FTLEtBQUtFLEtBQUwsQ0FBVyxlQUFYO0FBQ0FGLEtBQUtHLGdCQUFMLENBQXNCLFFBQXRCLEVBQWdDLFVBQVNDLEtBQVQsRUFBZ0I7QUFDNUMsUUFBSUMsZUFBZUMsU0FBU0MsY0FBVCxDQUF3QixhQUF4QixDQUFuQjtBQUNBLFFBQUlILE1BQU1JLEtBQVYsRUFBaUI7QUFDYkgscUJBQWFJLFdBQWIsR0FBMkJMLE1BQU1JLEtBQU4sQ0FBWUUsT0FBdkM7QUFDSCxLQUZELE1BRU87QUFDSEwscUJBQWFJLFdBQWIsR0FBMkIsRUFBM0I7QUFDSDtBQUNKLENBUEQ7O0FBU0E7QUFDQSxJQUFJRSxPQUFPTCxTQUFTQyxjQUFULENBQXdCLGNBQXhCLENBQVg7QUFDQUksS0FBS1IsZ0JBQUwsQ0FBc0IsUUFBdEIsRUFBZ0MsVUFBU0MsS0FBVCxFQUFnQjtBQUM1Q0EsVUFBTVEsY0FBTjs7QUFFQXRCLFdBQU91QixXQUFQLENBQW1CYixJQUFuQixFQUF5QmMsSUFBekIsQ0FBOEIsVUFBU0MsTUFBVCxFQUFpQjtBQUMzQyxZQUFJQSxPQUFPUCxLQUFYLEVBQWtCO0FBQ2Q7QUFDQSxnQkFBSVEsZUFBZVYsU0FBU0MsY0FBVCxDQUF3QixhQUF4QixDQUFuQjtBQUNBUyx5QkFBYVAsV0FBYixHQUEyQk0sT0FBT1AsS0FBUCxDQUFhRSxPQUF4QztBQUNILFNBSkQsTUFJTztBQUNIO0FBQ0FPLCtCQUFtQkYsT0FBT0csS0FBMUI7QUFDSDtBQUNKLEtBVEQ7QUFVSCxDQWJEOztBQWVBLFNBQVNELGtCQUFULENBQTRCQyxLQUE1QixFQUFtQztBQUMvQjtBQUNBLFFBQUlQLE9BQU9MLFNBQVNDLGNBQVQsQ0FBd0IsY0FBeEIsQ0FBWDtBQUNBLFFBQUlZLGNBQWNiLFNBQVNjLGFBQVQsQ0FBdUIsT0FBdkIsQ0FBbEI7QUFDQUQsZ0JBQVlFLFlBQVosQ0FBeUIsTUFBekIsRUFBaUMsUUFBakM7QUFDQUYsZ0JBQVlFLFlBQVosQ0FBeUIsTUFBekIsRUFBaUMsYUFBakM7QUFDQUYsZ0JBQVlFLFlBQVosQ0FBeUIsT0FBekIsRUFBa0NILE1BQU1JLEVBQXhDO0FBQ0FYLFNBQUtZLFdBQUwsQ0FBaUJKLFdBQWpCOztBQUVBO0FBQ0FSLFNBQUthLE1BQUw7QUFDSCxDIiwiZmlsZSI6ImFwcC5qcyIsInNvdXJjZXNDb250ZW50IjpbIiBcdC8vIFRoZSBtb2R1bGUgY2FjaGVcbiBcdHZhciBpbnN0YWxsZWRNb2R1bGVzID0ge307XG5cbiBcdC8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG4gXHRmdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cbiBcdFx0Ly8gQ2hlY2sgaWYgbW9kdWxlIGlzIGluIGNhY2hlXG4gXHRcdGlmKGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdKSB7XG4gXHRcdFx0cmV0dXJuIGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdLmV4cG9ydHM7XG4gXHRcdH1cbiBcdFx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcbiBcdFx0dmFyIG1vZHVsZSA9IGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdID0ge1xuIFx0XHRcdGk6IG1vZHVsZUlkLFxuIFx0XHRcdGw6IGZhbHNlLFxuIFx0XHRcdGV4cG9ydHM6IHt9XG4gXHRcdH07XG5cbiBcdFx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG4gXHRcdG1vZHVsZXNbbW9kdWxlSWRdLmNhbGwobW9kdWxlLmV4cG9ydHMsIG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG4gXHRcdC8vIEZsYWcgdGhlIG1vZHVsZSBhcyBsb2FkZWRcbiBcdFx0bW9kdWxlLmwgPSB0cnVlO1xuXG4gXHRcdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG4gXHRcdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbiBcdH1cblxuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZXMgb2JqZWN0IChfX3dlYnBhY2tfbW9kdWxlc19fKVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5tID0gbW9kdWxlcztcblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGUgY2FjaGVcbiBcdF9fd2VicGFja19yZXF1aXJlX18uYyA9IGluc3RhbGxlZE1vZHVsZXM7XG5cbiBcdC8vIGlkZW50aXR5IGZ1bmN0aW9uIGZvciBjYWxsaW5nIGhhcm1vbnkgaW1wb3J0cyB3aXRoIHRoZSBjb3JyZWN0IGNvbnRleHRcbiBcdF9fd2VicGFja19yZXF1aXJlX18uaSA9IGZ1bmN0aW9uKHZhbHVlKSB7IHJldHVybiB2YWx1ZTsgfTtcblxuIFx0Ly8gZGVmaW5lIGdldHRlciBmdW5jdGlvbiBmb3IgaGFybW9ueSBleHBvcnRzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQgPSBmdW5jdGlvbihleHBvcnRzLCBuYW1lLCBnZXR0ZXIpIHtcbiBcdFx0aWYoIV9fd2VicGFja19yZXF1aXJlX18ubyhleHBvcnRzLCBuYW1lKSkge1xuIFx0XHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBuYW1lLCB7XG4gXHRcdFx0XHRjb25maWd1cmFibGU6IGZhbHNlLFxuIFx0XHRcdFx0ZW51bWVyYWJsZTogdHJ1ZSxcbiBcdFx0XHRcdGdldDogZ2V0dGVyXG4gXHRcdFx0fSk7XG4gXHRcdH1cbiBcdH07XG5cbiBcdC8vIGdldERlZmF1bHRFeHBvcnQgZnVuY3Rpb24gZm9yIGNvbXBhdGliaWxpdHkgd2l0aCBub24taGFybW9ueSBtb2R1bGVzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm4gPSBmdW5jdGlvbihtb2R1bGUpIHtcbiBcdFx0dmFyIGdldHRlciA9IG1vZHVsZSAmJiBtb2R1bGUuX19lc01vZHVsZSA/XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0RGVmYXVsdCgpIHsgcmV0dXJuIG1vZHVsZVsnZGVmYXVsdCddOyB9IDpcbiBcdFx0XHRmdW5jdGlvbiBnZXRNb2R1bGVFeHBvcnRzKCkgeyByZXR1cm4gbW9kdWxlOyB9O1xuIFx0XHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQoZ2V0dGVyLCAnYScsIGdldHRlcik7XG4gXHRcdHJldHVybiBnZXR0ZXI7XG4gXHR9O1xuXG4gXHQvLyBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGxcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubyA9IGZ1bmN0aW9uKG9iamVjdCwgcHJvcGVydHkpIHsgcmV0dXJuIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChvYmplY3QsIHByb3BlcnR5KTsgfTtcblxuIFx0Ly8gX193ZWJwYWNrX3B1YmxpY19wYXRoX19cbiBcdF9fd2VicGFja19yZXF1aXJlX18ucCA9IFwiL2J1aWxkL1wiO1xuXG4gXHQvLyBMb2FkIGVudHJ5IG1vZHVsZSBhbmQgcmV0dXJuIGV4cG9ydHNcbiBcdHJldHVybiBfX3dlYnBhY2tfcmVxdWlyZV9fKF9fd2VicGFja19yZXF1aXJlX18ucyA9IFwiLi9hc3NldHMvanMvbWFpbi5qc1wiKTtcblxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyB3ZWJwYWNrL2Jvb3RzdHJhcCBhZjNkZjIzYmRjM2I3MDA1ZjZlNSIsInZhciBlbGVtZW50cyA9IHN0cmlwZS5lbGVtZW50cygpO1xuXG4vLyBDdXN0b20gc3R5bGluZyBjYW4gYmUgcGFzc2VkIHRvIG9wdGlvbnMgd2hlbiBjcmVhdGluZyBhbiBFbGVtZW50LlxudmFyIHN0eWxlID0ge1xuICAgIGJhc2U6IHtcbiAgICAgICAgY29sb3I6ICcjMzIzMjVkJyxcbiAgICAgICAgbGluZUhlaWdodDogJzI0cHgnLFxuICAgICAgICBmb250RmFtaWx5OiAnXCJIZWx2ZXRpY2EgTmV1ZVwiLCBIZWx2ZXRpY2EsIHNhbnMtc2VyaWYnLFxuICAgICAgICBmb250U21vb3RoaW5nOiAnYW50aWFsaWFzZWQnLFxuICAgICAgICBmb250U2l6ZTogJzE2cHgnLFxuICAgICAgICAnOjpwbGFjZWhvbGRlcic6IHtcbiAgICAgICAgICAgIGNvbG9yOiAnI2FhYjdjNCdcbiAgICAgICAgfVxuICAgIH0sXG4gICAgaW52YWxpZDoge1xuICAgICAgICBjb2xvcjogJyNmYTc1NWEnLFxuICAgICAgICBpY29uQ29sb3I6ICcjZmE3NTVhJ1xuICAgIH1cbn07XG5cbi8vIENyZWF0ZSBhbiBpbnN0YW5jZSBvZiB0aGUgY2FyZCBFbGVtZW50XG52YXIgY2FyZCA9IGVsZW1lbnRzLmNyZWF0ZSgnY2FyZCcsIHtzdHlsZTogc3R5bGV9KTtcblxuLy8gQWRkIGFuIGluc3RhbmNlIG9mIHRoZSBjYXJkIEVsZW1lbnQgaW50byB0aGUgYGNhcmQtZWxlbWVudGAgPGRpdj5cbmNhcmQubW91bnQoJyNjYXJkLWVsZW1lbnQnKTtcbmNhcmQuYWRkRXZlbnRMaXN0ZW5lcignY2hhbmdlJywgZnVuY3Rpb24oZXZlbnQpIHtcbiAgICB2YXIgZGlzcGxheUVycm9yID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2NhcmQtZXJyb3JzJyk7XG4gICAgaWYgKGV2ZW50LmVycm9yKSB7XG4gICAgICAgIGRpc3BsYXlFcnJvci50ZXh0Q29udGVudCA9IGV2ZW50LmVycm9yLm1lc3NhZ2U7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgZGlzcGxheUVycm9yLnRleHRDb250ZW50ID0gJyc7XG4gICAgfVxufSk7XG5cbi8vIENyZWF0ZSBhIHRva2VuIG9yIGRpc3BsYXkgYW4gZXJyb3Igd2hlbiB0aGUgZm9ybSBpcyBzdWJtaXR0ZWQuXG52YXIgZm9ybSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdwYXltZW50LWZvcm0nKTtcbmZvcm0uYWRkRXZlbnRMaXN0ZW5lcignc3VibWl0JywgZnVuY3Rpb24oZXZlbnQpIHtcbiAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuXG4gICAgc3RyaXBlLmNyZWF0ZVRva2VuKGNhcmQpLnRoZW4oZnVuY3Rpb24ocmVzdWx0KSB7XG4gICAgICAgIGlmIChyZXN1bHQuZXJyb3IpIHtcbiAgICAgICAgICAgIC8vIEluZm9ybSB0aGUgdXNlciBpZiB0aGVyZSB3YXMgYW4gZXJyb3JcbiAgICAgICAgICAgIHZhciBlcnJvckVsZW1lbnQgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnY2FyZC1lcnJvcnMnKTtcbiAgICAgICAgICAgIGVycm9yRWxlbWVudC50ZXh0Q29udGVudCA9IHJlc3VsdC5lcnJvci5tZXNzYWdlO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgLy8gU2VuZCB0aGUgdG9rZW4gdG8geW91ciBzZXJ2ZXJcbiAgICAgICAgICAgIHN0cmlwZVRva2VuSGFuZGxlcihyZXN1bHQudG9rZW4pO1xuICAgICAgICB9XG4gICAgfSk7XG59KTtcblxuZnVuY3Rpb24gc3RyaXBlVG9rZW5IYW5kbGVyKHRva2VuKSB7XG4gICAgLy8gSW5zZXJ0IHRoZSB0b2tlbiBJRCBpbnRvIHRoZSBmb3JtIHNvIGl0IGdldHMgc3VibWl0dGVkIHRvIHRoZSBzZXJ2ZXJcbiAgICB2YXIgZm9ybSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdwYXltZW50LWZvcm0nKTtcbiAgICB2YXIgaGlkZGVuSW5wdXQgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdpbnB1dCcpO1xuICAgIGhpZGRlbklucHV0LnNldEF0dHJpYnV0ZSgndHlwZScsICdoaWRkZW4nKTtcbiAgICBoaWRkZW5JbnB1dC5zZXRBdHRyaWJ1dGUoJ25hbWUnLCAnc3RyaXBlVG9rZW4nKTtcbiAgICBoaWRkZW5JbnB1dC5zZXRBdHRyaWJ1dGUoJ3ZhbHVlJywgdG9rZW4uaWQpO1xuICAgIGZvcm0uYXBwZW5kQ2hpbGQoaGlkZGVuSW5wdXQpO1xuXG4gICAgLy8gU3VibWl0IHRoZSBmb3JtXG4gICAgZm9ybS5zdWJtaXQoKTtcbn1cblxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyAuL2Fzc2V0cy9qcy9tYWluLmpzIl0sInNvdXJjZVJvb3QiOiIifQ==