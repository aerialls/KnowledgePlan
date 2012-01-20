const PLAYER_PLAY = 0;
const PLAYER_PAUSE = 1;

function Player()
{
    this.state = undefined;
    this.api = undefined;
}

/**
 * Initialize the player
 */
Player.prototype.initialize = function()
{
    $(":range").rangeinput({
        min:  simulation.minTime,
        max:  simulation.maxTime,
        step: simulation.step
    });

    var _player = this;
    this.api = $(':range').data('rangeinput');

    // (Play|Pause) button
    $('#play-btn').click(function() {
        _player.changeState();

        return false;
    });

    // Backward button
    $('#backward-btn').click(function () {
        _player.backward();

        return false;
    });

    // >| Button
    $('#forward-btn').click(function () {
        _player.forward();

        return false;
    });

    $(":range").change(function(event, value) {
	_player.move(value);
    });

    this.backward();
}

/**
 * Backward button
 */
Player.prototype.backward = function()
{
    this.pause();
    this.move(simulation.minTime);
}

/**
 * Forward button
 */
Player.prototype.forward = function()
{
    this.pause();
    this.move(simulation.maxTime);
}

Player.prototype.setInformations = function(values)
{
    $("#label-time").html(values['time']+' sec');
    $('#label-accepted-flows').html(values['flows_accepted']);
    $('#label-rejected-flows').html(values['flows_rejected']);
}

Player.prototype.play = function()
{
    if (this.state == PLAYER_PLAY) {
        return;
    }

    this.state = PLAYER_PLAY;
    $('#play-btn > img').attr({src: '/images/pause.png'});
}

Player.prototype.pause = function()
{
    if (this.state == PLAYER_PAUSE) {
        return;
    }

    this.state = PLAYER_PAUSE;
    $('#play-btn > img').attr({src: '/images/play.png'});
}

Player.prototype.move = function(time)
{
    if (time in simulation.informations) {
        this.setInformations(simulation.informations[time]);
    } else {
        console.log('Unable to find informations for the time ' + time);
    }

    this.api.setValue(time);
}

Player.prototype.changeState = function()
{
    if (this.state == PLAYER_PAUSE) {
        this.play();
    } else {
        this.pause();
    }
}

var player = new Player();

$(document).ready(function() {
    player.initialize();
});