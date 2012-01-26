const PLAYER_PLAY = 0;
const PLAYER_PAUSE = 1;

function Player()
{
    this.state    = undefined;
    this.api      = undefined;
    this.timePlot = undefined;
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

    // Forward button
    $('#forward-btn').click(function () {
        _player.forward();

        return false;
    });

    $(":range").change(function(event, value) {
	_player.move(value);
    });

    $(document).keydown(function(event) {
        switch (event.keyCode) {
            case 32:
                _player.changeState();
                break;
            case 37:
                _player.move(_player.calculateTime(_player.getPosition() - 100 * simulation.step));
                break;
            case 39:
                _player.move(_player.calculateTime(_player.getPosition() + 100 * simulation.step));
                break;
        }
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

/**
 * Displays informations in the table next to the plot
 */
Player.prototype.displayInformations = function(values)
{
    var time = values['time'];

    if (time.indexOf('.') == -1) {
        time = time + '.0';
    }

    $("#label-time").html(time + ' sec');
    $('#label-accepted-flows').html(values['flows_accepted']);
    $('#label-rejected-flows').html(values['flows_rejected']);
    $('#label-waiting-time').html(values['waiting_time'] + ' ns');
    $('#label-outputrate').html(values['outputrate']);
    $('#label-delay').html(values['delay']);
}

/**
 * Displays the plot
 */
Player.prototype.displayPlot = function(values)
{
    if (values['time'] == this.timePlot) {
        // We don't need to draw the same plot
        return;
    }

    this.timePlot = values['time'];

    $.plot($("#chart"), [
        {
            data: values['points'],
            points: {show: true},
            color: '#ffd658'
        },
        {
            data: values['centroids'],
            points: {show: true},
            color: 8
        },
        {
            data: values['hlm'],
            lines: {show: true},
            color: 7
        }
    ],{
        grid:  {hoverable: true},
        xaxis: {min: simulation.plotOptions['x_min'], max: simulation.plotOptions['x_max']},
        yaxis: {min: simulation.plotOptions['y_min'], max: simulation.plotOptions['y_max']}
    });
}

Player.prototype.play = function()
{
    if (this.state == PLAYER_PLAY) {
        return;
    }

    this.state = PLAYER_PLAY;
    $('#play-btn > img').attr({src: '/images/pause.png'});

    // Start the loop
    console.log('Starting the loop');
    this.loop();
}

/**
 * Do a movement of the player
 */
Player.prototype.loop = function()
{
    if (this.state != PLAYER_PLAY) {
        console.log('The player is turn off. Stoping the loop');
        return;
    }

    if (this.getPosition() >= simulation.maxTime) {
        console.log('End of the simulation');
        this.pause();
        return;
    }

    // We need a Math.round here because Javascript can't add
    // float exactly. And we need the exact number (2.1 and not
    // 2.1000000000001 ) for the map (simulation.informations)
    var nextStep = this.calculateTime(this.getPosition() + simulation.step);

    this.move(nextStep);

    // Do again...
    var _player = this;
    setTimeout(function() {
        _player.loop();
    }, 100);
}

Player.prototype.pause = function()
{
    if (this.state == PLAYER_PAUSE) {
        return;
    }

    this.state = PLAYER_PAUSE;
    $('#play-btn > img').attr({src: '/images/play.png'});
}

/**
 * Moves the player to the specified time
 */
Player.prototype.move = function(time)
{
    // Min check
    if (time < simulation.minTime) {
        time = simulation.minTime;
    }

    // Max check
    if (time > simulation.maxTime) {
        time = simulation.maxTime;
    }

    if (time != this.getPosition()) {
        this.api.setValue(time);
    }

    // Informations
    if (time in simulation.informations) {
        this.displayInformations(simulation.informations[time]);
    } else {
        console.warn('Unable to find informations for the time ' + time);
    }

    // Plots
    if (time in simulation.plots) {
        this.displayPlot(simulation.plots[time]);
    } else {
        // We need to seach the last plot in the list
        var plot = this.searchPreviousPlot(time);
        if (plot != null) {
            this.displayPlot(plot);
        } else {
            // No chart to display yet
            // So we erase the last one
            $('#chart').html('');
        }
    }
}

/**
 * Search the previous plot for a given time
 */
Player.prototype.searchPreviousPlot = function(time)
{
    var currentTime = time - simulation.step;

    // Search for a previous plot
    while (currentTime >= simulation.minTime) {
        // Does the plot exist?
        if (currentTime in simulation.plots) {
            return simulation.plots[currentTime];
        }

        currentTime = this.calculateTime(currentTime - simulation.step);
    }

    // Humm... we can't be here
    console.warn('Unable to find the previous plot for time ' + time);

    return null;
}

/**
 * Change the state of the player.
 * If the player is paused then the player is turned on
 * And vice versa
 */
Player.prototype.changeState = function()
{
    if (this.state == PLAYER_PAUSE) {
        this.play();
    } else {
        this.pause();
    }
}

/**
 * Round a simulation time
 */
Player.prototype.calculateTime = function(time)
{
    return Math.round(time * 10) / 10;
}

/**
 * Returns the position of the player
 */
Player.prototype.getPosition = function()
{
    return this.api.getValue();
}

var player = new Player();

$(document).ready(function() {
    // Get the simulation (ajax loading)
    $(document).ajaxError(function(e, jqxhr, settings, exception) {
        if (settings.dataType == 'script') {
            alert('Unable to load the simulation.');
            $.unblockUI();
        }
    });

    $(document).ajaxStart(function() {
        $.blockUI({message: '<div id="loading"><img src="/images/ajax-loader.gif"/> Loading...</div>'});
    });

    $(document).ajaxStop(function() {
        $.unblockUI();
    });

    $.getScript('/js/simulation.js', function(data, textStatus){
        player.initialize();
    });
});