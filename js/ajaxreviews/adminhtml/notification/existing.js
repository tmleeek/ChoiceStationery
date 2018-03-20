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

/**
 * Directive for existing orders form
 *
 * @returns {{restrict: string, templateUrl: string, scope: boolean, controller: notificationExistingController}}
 * @constructor
 */
var MpAjaxReviewsNotificationExistingDirective = function () {
    return {
        restrict: 'C',
        templateUrl: 'magpleasure/ajaxreviews/adminhtml/notification/existing/template.html',
        scope: true,
        controller: notificationExistingController
    }
}

/**
 * Controller for existing orders directive
 *
 * @param $scope
 * @param $timeout
 * @param $http
 * @param $q
 * @param ajaxReviewsExistingOrders
 */
var notificationExistingController = function ($scope, $timeout, $http, $q, ajaxReviewsExistingOrders) {
    $scope.periods = ajaxReviewsExistingOrders.getPeriods();
    $scope.interval = {
        interval: 3,
        min: 1,
        max: Object.keys($scope.periods).length
    };
    $scope.info = {
        orders: '-',
        items: '-',
        notifications: '-'
    }
    $scope.canceller = false;
    var oldInterval = $scope.interval.interval;

    $scope.checkInterval = function () {
        $timeout(function () {
            if (oldInterval != $scope.interval.interval) {
                $scope.error = false;
                oldInterval = $scope.interval.interval;
                resetInfo();
                getInfo();
            }
        })
    }

    getInfo();

    function getHttpData(url, parameters) {
        var deferred = $q.defer();
        $http.post(url, parameters, {timeout: $scope.canceller.promise}).then(function (response) {
            deferred.resolve(response);
        }, function (error) {
            if (0 == error.status) {
                deferred.resolve();
            } else {
                deferred.resolve({ error: true });
            }
        })
        return deferred.promise;
    }

    function getInfo() {
        if ($scope.canceller) {
            $scope.canceller.resolve();
        }
        $scope.canceller = $q.defer();
        var parameters = {
                form_key: ajaxReviewsExistingOrders.getFormKey(),
                interval: $scope.interval.interval
            },
            promises = [];
        promises.push(getHttpData(ajaxReviewsExistingOrders.getOrdersUrl(), parameters));
        promises.push(getHttpData(ajaxReviewsExistingOrders.getItemsUrl(), parameters));
        promises.push(getHttpData(ajaxReviewsExistingOrders.getNotificationsUrl(), parameters));
        $q.all(promises).then(function (responses) {
            angular.forEach(responses, function (response) {
                if (!$scope.error && response && 'data' in response) {
                    response = response.data;
                    if ('error' in response) {
                        $scope.error = true;
                        resetInfo();
                    } else if ('orders' in response) {
                        $scope.info.orders = response.orders;
                    } else if ('items' in response) {
                        $scope.info.items = response.items;
                    } else if ('notifications' in response) {
                        $scope.info.notifications = response.notifications;
                    }
                }
            })
            $scope.canceller = false;
        }).then(/**never happens because getHttpData function always resolve promises*/);
    }

    function resetInfo() {
        $scope.info.orders = $scope.info.items = $scope.info.notifications = '-';
    }
}
