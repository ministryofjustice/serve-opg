import jquery from 'jquery'

const $ = jquery;

// SESSION TIMEOUT POPUP LOGIC
const SessionTimeoutDialog = function (options) {
    var that = this;
    this.element = options.element;
    this.sessionExpiresMs = options.sessionExpiresMs;
    this.sessionPopupShowAfterMs = options.sessionPopupShowAfterMs;
    this.keepSessionAliveUrl = options.keepSessionAliveUrl;
    this.redirectAfterMs = 3000;

    //debugger;

    var $okButton = that.element.find('.js-ok-button'),
        $underlay = $('.session-timeout-underlay');

    // attach click event
    $okButton.click(function (e) {
        e.preventDefault();
        that.hidePopupAndRestartCountdown();
    });

    this.startCountdown = function () {

        this.countDownPopup = window.setInterval(function () {
            that.element.css('visibility', 'visible');
            $underlay.css(
                {
                    'visibility': 'visible',
                    'height': $(document).height() + 'px'
                });

        }, this.sessionPopupShowAfterMs);

        this.countDownLogout = window.setInterval(function () {
           // click log out button
           that.element.find('.js-signout-button').click();
        }, this.sessionExpiresMs + this.redirectAfterMs);
    };

    this.hidePopupAndRestartCountdown = function () {
        this.element.hide();
        $underlay.hide();

        this.keepSessionAlive();
        // restart countdown
        window.clearInterval(this.countDownPopup);
        window.clearInterval(this.countDownLogout);
        this.startCountdown();
    };

    this.keepSessionAlive = function () {
        $.get(this.keepSessionAliveUrl + '?refresh=' + Date.now());
    };

};

export default SessionTimeoutDialog
