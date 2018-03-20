/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE.txt
 *
 * @category   Magpleasure
 * @package    Magpleasure_Ajaxreviews
 * @copyright  Copyright (c) 2014-2015 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE.txt
 */

var MpAjaxReviewsSliderDirective = function ($compile, $timeout) {
    return {
        restrict: 'E',
        scope: {
            rangeInterval: '=rangeInterval',
            minRange: '=minRange',
            maxRange: '=maxRange'
        },

        /** Changed because of incompatibility with IE8 */
//        template: '<div class="mp-slider-track" ng-class="activeSlider" '
//            + 'ng-mousemove="onPointerOrMouseMove($event)" ng-mouseleave="onPointerOrMouseLeave($event)" '
//            + 'ng-click="onTrackClick($event)">'
//            + '<div class="mp-slider-fill">'
//            + '<span class="mp-slider-handler" '
//            + 'ng-mousedown="onPointerOrMouseDown($event)">'
//            + '</span>'
//            + '</div>'
//            + '</div>'

        link: function (scope, element, attr, ctrl) {
            var sliderElement = angular.element(
                '<div class="mp-slider-track" ng-class="activeSlider" ng-mousemove="onPointerOrMouseMove($event)" ng-mouseleave="onPointerOrMouseLeave($event)" ng-click="onTrackClick($event)">' +
                    '<div class="mp-slider-fill">' +
                        '<span class="mp-slider-handler" ng-mousedown="onPointerOrMouseDown($event)"></span>' +
                    '</div>' +
                '</div>');
            $compile(sliderElement)(scope);
            element.append(sliderElement);

            var rangeInterval = scope.rangeInterval,
                startX = false,
                clickedHandlerByMouseFlag = false,
                leave = false;

            var handlerTrack = element.find('div')[0],
                handlerFill = element.find('div')[1],
                handler = element.find('span')[0];

            handler.style.top = (handlerTrack.offsetHeight / 2) - (handler.offsetHeight / 2) + 'px';
            var handlerWidth = handler.offsetWidth,
                handlerTrackWidth = handlerTrack.clientWidth - handlerWidth,
                handlerLeftPos = parseInt(handler.style.left, 10),
                leftIndent = handlerTrack.getBoundingClientRect().left;

            scope.$watch('rangeInterval + minRange + maxRange', function () {
                rangeInterval = scope.rangeInterval;
                setHandlerPosition();
            });

            scope.onPointerOrMouseMove = function (e) {
                if (clickedHandlerByMouseFlag) {
                    scope.activeSlider = 'mp-slider-active';
                    var distance = parseInt(e.clientX, 10) - startX,
                        leftPos = filterHandlerPosition(handlerLeftPos + distance);
                    setInterval(leftPos);
                }
            }

            scope.onPointerOrMouseLeave = function (e) {
                if (clickedHandlerByMouseFlag) {
                    leave = true;
                    updateInterval(e);
                }
            }

            scope.onPointerOrMouseDown = function (e) {
                clickedHandlerByMouseFlag = true;
                handlerLeftPos = parseInt(handler.style.left, 10);
                startX = parseInt(e.clientX, 10);
            }

            scope.onTrackClick = function (e) {
                if (leave) {
                    leave = false;
                } else {
                    var offsetX = e.clientX - leftIndent,
                        distance = filterHandlerPosition(offsetX - (handler.offsetWidth / 2));
                    setInterval(distance);
                    $timeout(function () {
                        scope.activeSlider = '';
                        clickedHandlerByMouseFlag = false;
                    })
                }
            }

            function updateInterval(e) {
                var distance = parseInt(e.clientX, 10) - startX,
                    leftPos = filterHandlerPosition(handlerLeftPos + distance);
                setInterval(leftPos);
                $timeout(function () {
                    scope.activeSlider = '';
                    clickedHandlerByMouseFlag = false;
                })
            }

            function calculateSlider(obj) {
                handlerTrackWidth = handlerTrack.clientWidth - handlerWidth;
                return {x: handlerTrackWidth, x1: 0, x2: obj.x2, y: scope.maxRange, y1: scope.minRange, y2: obj.y2};
            }

            function calculateHandlerPosition() {
                var F = calculateSlider({x2: '', y2: rangeInterval}),
                    eq1 = F.y - F.y1 ,
                    eq2 = F.y2 - F.y1,
                    eq3 = F.x - F.x1;
                F.x2 = (eq2 * eq3) / eq1 + F.x1;
                return F.x2;
            }

            function calculateInterval(x2) {
                var F = calculateSlider({x2: x2, y2: ''}),
                    eq1 = F.y - F.y1,
                    eq2 = F.x - F.x1,
                    eq3 = F.x2 - F.x1;
                F.y2 = ((eq1 * eq3) / eq2) + F.y1;
                return F.y2;
            }

            function filterHandlerPosition(handlerLeftPos) {
                return (handlerLeftPos > handlerTrackWidth) ? handlerTrackWidth : (handlerLeftPos < 0) ? 0 : handlerLeftPos;
            }

            function setHandlerPosition() {
                var x2 = filterHandlerPosition(calculateHandlerPosition());
                handler.style.left = x2 + 'px';
                handlerFill.style.width = x2 + (handler.offsetWidth / 2) + 'px';
            }

            function setInterval(leftPos) {
                scope.rangeInterval = Math.round(calculateInterval(leftPos));
            }
        }
    }
}