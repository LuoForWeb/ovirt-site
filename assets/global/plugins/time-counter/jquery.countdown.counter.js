(function($) {
"use strict";

var gTicksLeft = 0;

var digit1 = 0;
var digit2 = 0;
var digit3 = 0;
var digit4 = 0;
var digit5 = 0;
var digit6 = 0;


var gIntervalToken = null;

var getTicksLeft = function() {
    return gTicksLeft;
};

var decTicksLeft = function() {
    gTicksLeft--;
};

var removeAllDigits = function($element) {
    $element.removeClass("digit0 digit1 digit2 digit3 digit4 digit5 digit6 digit7 digit8 digit9");
};

var setItem = function(itemNumber, digit) {
    var token = "#counter_item" + itemNumber + " :first-child";
    var $element = $(token).next(); // second child

    removeAllDigits($element);
    $element.addClass("digit" + digit);
};

var calculateDigits = function() {
	var hoursLeft = Math.floor(getTicksLeft() / 3600);
    var minutesLeft = Math.floor((getTicksLeft() - hoursLeft * 3600)/60);
    var secondsLeft = getTicksLeft() - hoursLeft * 3600 - minutesLeft * 60;
    digit1 = Math.floor(hoursLeft / 10);
    digit2 = hoursLeft - digit1 * 10;
	
	digit3 = Math.floor(minutesLeft / 10);
    digit4 = minutesLeft - digit3 * 10;

    digit5 = Math.floor(secondsLeft / 10);
    digit6 = secondsLeft - digit5 * 10;

};

var init = function() {
    calculateDigits();
    setItem(1, digit1);
    setItem(2, digit2);
    setItem(3, digit3);
    setItem(4, digit4);
	setItem(5, digit5);
    setItem(6, digit6);
};

var switchItem = function(itemNumber, digit, capacity) {
    var nextDigit = (digit === 0) ? capacity : (digit - 1);

    //$("#log2").text("digit" + digit + ", next digit: " + nextDigit);

    var token = "#counter_item" + itemNumber + " :first-child";
    var $element = $(token).next(); // second child

    removeAllDigits($element);
    $element.addClass("digit" + digit);
    $element.after('<div class="digit digit' + nextDigit + '" style="margin-top: 55px"></div>');

    var $newElement = $element.next();
    $element.animate({
        "margin-top": -55
    }, 500, function () { $element.remove(); });

    $newElement.animate({
        "margin-top": 0
    }, 500);

};

var tick = function()
{
    calculateDigits();

    if(digit6 === 0) {
        if (digit5 === 0) {
            if (digit4 === 0) {
				if (digit3 == 0){
					if (digit2 == 0){
						switchItem(1, digit1, 5);
					}
					switchItem(2, digit2, 9);
				}
                switchItem(3, digit3, 5);
            }
            switchItem(4, digit4, 9);
        }
        switchItem(5, digit5, 5);
    }
    switchItem(6, digit6, 9);

    decTicksLeft();

    if (getTicksLeft() === 0) {
        clearInterval(gIntervalToken);
        gIntervalToken = null;
    }
};

window.CounterInit = function(ticksCount) {
    if (ticksCount === null || isNaN(ticksCount)) {
        ticksCount = 10 * 60;
    }
    gTicksLeft = ticksCount;
    init();
    if (gIntervalToken !== null) {
        clearInterval(gIntervalToken);
        gIntervalToken = null;
    }
    // strange chrome bug workaround
    $.timeout(function() {
        gIntervalToken = $.interval(tick, 1000);
    }, 100);
};



})(jQuery);
