
/**
 * If the value of name matches any of the keys in the object,
 * it returns true. Now, I personally find that looks a little
 * ugly since we have to assign values to each of our keys
 * in order to get this to work. So, I made a quick function
 * that simply converts an array into an object.
 *
 * See http://snook.ca/archives/javascript/testing_for_a_v
 */
function oc(a)
{
    var o = {};
    for(var i = 0 ; i < a.length ; i++) {
        o[a[i]]='';
    }

    return o;
}

const PLAYER_PLAY = 0;
const PLAYER_PAUSE = 1;

function Player()
{
    this.state    = undefined;
    this.api      = undefined;
}

/**
 * Initialize the player
 */
Player.prototype.initialize = function()
{
    $(":range").rangeinput({
        min:  experience.minTime,
        max:  experience.maxTime,
        step: experience.step
    });

    // For each simulations
    for (var i = 0 ; i < experience.options['simulations'].length ; i++) {

        var name = experience.options['simulations'][i];
        $('#template').clone()
                      .attr('id', 'simul-' + name)
                      .attr('data-name', name)
                      .attr('data-id', i)
                      .appendTo('#experience');

        $('#simul-' + name + ' .title').html(name.replace('_', ' '));
    }

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

    // onChange event
    $(":range").change(function(event, value) {
        _player.move(value);
    });

    $(document).keydown(function(event) {
        switch (event.keyCode) {
            // Space
            case 32:
                _player.changeState();
                break;
            // <-
            case 37:
                _player.move(_player.calculateTime(_player.getPosition() - 100 * experience.step));
                break;
            // ->
            case 39:
                _player.move(_player.calculateTime(_player.getPosition() + 100 * experience.step));
                break;
        }
    });

    // Change the title in the topbar
    $('.topbar .brand').html(experience.options['title']);

    // and finaly, reset the player
    this.backward();
}

/**
 * Backward button
 */
Player.prototype.backward = function()
{
    this.pause();
    this.move(experience.minTime);
}

/**
 * Forward button
 */
Player.prototype.forward = function()
{
    this.pause();
    this.move(experience.maxTime);
}

/**
 * Displays informations for a simulation
 */
Player.prototype.displayInformations = function(name, values)
{
    $('#simul-' + name + ' .label-time').html(values['time'] + ' sec');
    $('#simul-' + name + ' .label-accepted-flows').html(values['flows_accepted']);
    $('#simul-' + name + ' .label-rejected-flows').html(values['flows_rejected']);
    $('#simul-' + name + ' .label-waiting-time').html(values['waiting_time'] + ' ns');
    $('#simul-' + name + ' .label-outputrate').html(values['outputrate']);
    $('#simul-' + name + ' .label-outputrate-average').html(values['outputrate_average']);
    $('#simul-' + name + ' .label-delay').html(values['delay']);
}

/**
 * Displays the plot for a simulation
 */
Player.prototype.displayPlot = function(name, values)
{
    var simulation = experience.simulations[name];
    var plots      = new Array();
    var delayMax   = simulation['options']['delay_max'];

    // The options allows to disable a plot

    if ('points' in oc(experience.options['plots'])) {
        plots.push({
            data: values['points'],
            points: {show: true},
            color: '#ffd658',
            label: 'Measuring points'
        });
    }

    if ('centroids' in oc(experience.options['plots'])) {
        plots.push({
            data: values['centroids'],
            points: {show: true},
            color: 8,
            label: 'Centroids'
        });
    }

    if ('delay_max' in oc(experience.options['plots'])) {
        plots.push({
            data: [[simulation['options']['x_min'], delayMax], [simulation['options']['x_max'], delayMax]],
            lines: {show: true},
            color: '#c7c7c7',
            shadowSize: 0,
            label: 'Target QoS'
        });
    }

    if ('hlm' in oc(experience.options['plots'])) {
        plots.push({
            data: values['hlm'],
            lines: {show: true},
            color: 7,
            label: 'M/G/1 Queue'
        });
    }

    $.plot($('#simul-' + name + ' .chart'), plots, {
        grid:  {hoverable: true},
        xaxis: {
            min: simulation['options']['x_min'],
            max: simulation['options']['x_max'],
            label: simulation['options']['x_label']
        },
        yaxis: {
            min: simulation['options']['y_min'],
            max: simulation['options']['y_max'],
            label: simulation['options']['y_label']
        }
    });

    // Tooltip
    var previousPoint = null;
    $('#simul-' + name + ' .chart').bind('plothover', function (event, pos, item) {
        if (item) {
            if (previousPoint != item.dataIndex) {
                // We need to remove the previous tooltip
                $("#tooltip").remove();

                var x = item.datapoint[0].toFixed(2);
                var y = item.datapoint[1].toFixed(2);

                previousPoint = item.dataIndex;

                var tooltip = $('<div id="tooltip" class="tipsy">('+x+', '+y+')</div>').css({
                    top:  item.pageY + 5,
                    left: item.pageX + 7
                });

                tooltip.appendTo('body').fadeIn(200);
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }
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

    if (this.getPosition() >= experience.maxTime) {
        console.log('End of the experience');
        this.pause();
        return;
    }

    // We need a Math.round here because Javascript can't add
    // float exactly. And we need the exact number (2.1 and not
    // 2.1000000000001 ) for the map (simulation.informations)
    var nextStep = this.calculateTime(this.getPosition() + experience.step);

    var _player = this;
    setTimeout(function() {
        // Asynchronous call
        _player.move(nextStep);
    });

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
    if (time < experience.minTime) {
        time = experience.minTime;
    }

    // Max check
    if (time > experience.maxTime) {
        time = experience.maxTime;
    }

    if (time != this.getPosition()) {
        this.setPosition(time);
    }

    for (var i = 0 ; i < experience.options['simulations'].length ; i++) {
        var name       = experience.options['simulations'][i];
        var simulation = experience.simulations[name];

        // Informations
        if (time in simulation['informations']) {
            this.displayInformations(name, simulation['informations'][time]);
        }

        // Plots
        if (time in simulation['plots']) {
            this.displayPlot(name, simulation['plots'][time]);
        } else {
            // We need to seach the last plot in the list
            var plot = this.searchPreviousPlot(name,time);
            if (plot != null) {
                this.displayPlot(name, plot);
            } else {
                // No chart to display yet
                // So we erase the last one
                $('#chart').html('');
            }
        }
    }
}

/**
 * Search the previous plot for a given time
 */
Player.prototype.searchPreviousPlot = function(name, time)
{
    var currentTime = time - experience.step;
    var simulation  = experience.simulations[name];

    // Search for a previous plot
    while (currentTime >= experience.minTime) {
        // Does the plot exist?
        if (currentTime in simulation['plots']) {
            return simulation['plots'][currentTime];
        }

        currentTime = this.calculateTime(currentTime - experience.step);
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

Player.prototype.setPosition = function(value)
{
    this.api.setValue(value);
}

var player = new Player();

$(document).ready(function() {
    // Get the experience (ajax loading)
    $(document).ajaxError(function(e, jqxhr, settings, exception) {
        if (settings.dataType == 'script') {
            alert('Unable to load the simulation "' + file + '".');
            $.unblockUI();
        }
    });

    $(document).ajaxStart(function() {
        $.blockUI({message: '<div id="loading"><img src="/images/ajax-loader.gif"/> Loading...</div>'});
    });

    $(document).ajaxStop(function() {
        $.unblockUI();
    });

    $.getScript(file, function(data, textStatus){
        player.initialize();
    });
});