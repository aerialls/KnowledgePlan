const PLAYER_PLAY = 0;
const PLAYER_PAUSE = 1;

function Player()
{
    this.state = undefined;
}

/**
 * Initialize the player
 */
Player.prototype.initialize = function()
{
    var _player = this;
    $('#play-btn').click(function() {
        _player.changeState();

        return false;
    });

    var infos = simulation.informations['0'];

    this.pause();
    this.setInformations(infos);
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