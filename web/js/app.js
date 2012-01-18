const PLAYER_PLAY = 0;
const PLAYER_PAUSE = 1;

function Player()
{
    this.state = undefined;
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
    player.pause();

    $('#play-btn').click(function() {
        player.changeState();

        return false;
    });
});