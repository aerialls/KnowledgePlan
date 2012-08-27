KnowledgePlan website
=====================

Small guide to install the website (needs PHP 5.3.2 min)

## Installation

The website has several dependencies. To install these dependencies just run:

    php bin/vendors install

This script will download the dependencies in the `vendor` folder. For more
informations about Composer (the software to resolve these dependencies, see
https://github.com/composer/composer)

## Apache VirtualHost

The website needs a virtualhost pointing to the `web` folder. For instance:

    <VirtualHost *:80>
        DocumentRoot "/path/to/KnowledgePlan/web"
        ServerName www.knowledgeplan.com
        <Directory "/path/to/KnowledgePlan/web">
            AllowOverride All
            Allow from All
        </Directory>
    </VirtualHost>

## Configuration

All the configuration is done in the `bootstrap.php` in the `KnowledgePlanServiceProvider`.

```php
// Knowledge Plan
$app->register(new KnowledgePlanServiceProvider(), array(
    // Configuration goes here
));
```

### List of available options

<table>
    <tr>
        <th>Key</th>
        <th>Description</th>
        <th>Example</th>
    </tr>
    <tr>
        <td>kp.cache_folder</td>
        <td>The folder to store simulation files (cached)</td>
        <td>'cache/'</td>
    </tr>
    <tr>
        <td>kp.simulations_folder</td>
        <td>The folder where the output files are stored</td>
        <td>'simulations/'</td>
    </tr>
    <tr>
        <td>kp.dry_run</td>
        <td>
            By default, the simulation is store in the cache to speed
            up the following requests. If you want to always check the output
            file to create the simulation and never call the cache, set this
            option to true. The cache will always be ignored.
        </td>
        <td>false</td>
    </tr>
    <tr>
        <td>kp.options</td>
        <td>Options for all the plot</td>
        <td>See below for the list</td>
    </tr>
    <tr>
        <td>kp.simulation_options</td>
        <td>
            Options for a specific simulation (associative array).
            See `kp.options` for the list (it's the same).
        </td>
        <td>See below for the list</td>
    </tr>
    <tr>
        <td>kp.experiences</td>
        <td>The list of all the experiences</td>
        <td>See below for the list</td>
    </tr>
</table>


### `kp.options` options list

<table>
    <tr>
        <th>Key</th>
        <th>Description</th>
    </tr>
    <tr>
        <td>x_min</td>
        <td></td>
        <td>0</td>
    </tr>
    <tr>
        <td>x_max</td>
        <td></td>
        <td>10</td>
    </tr>
    <tr>
        <td>x_name</td>
        <td>The name of the field in the output file</td>
        <td>outputrate</td>
    </tr>
    <tr>
        <td>x_label</td>
        <td>
            The name of the label. If this field is left blank, the
            `x_name` field is using
        </td>
        <td>Outpute rate (packet/ms)</td>
    </tr>
    <tr>
        <td>y_min</td>
        <td></td>
        <td>0</td>
    </tr>
    <tr>
        <td>y_max</td>
        <td></td>
        <td>10</td>
    </tr>
    <tr>
        <td>y_name</td>
        <td>The name of the field in the output file</td>
        <td>delay</td>
    </tr>
    <tr>
        <td>y_label</td>
        <td>
            The name of the label. If this field is left blank, the
            `y_name` field is using
        </td>
        <td>Delay (ms)</td>
    </tr>
</table>

### `kp.experiences` options list

<table>
    <tr>
        <th>Key</th>
        <th>Description</th>
        <th>Example</th>
    </tr>
    <tr>
        <td>title</td>
        <td>The title of the experience (will be in the black title bar)</td>
        <td>My experience</td>
    </tr>
    <tr>
        <td>simulations</td>
        <td>An array of simulations. An empty array means all simulations</td>
        <td>array('poisson', 'other')</td>
    </tr>
    <tr>
        <td>simulations-exclude</td>
        <td>
            Each simulation in this array will be removed from the simulation
            array. It's usefull if you want display all the simulations without
            one.
        </td>
        <td>array('knowledge_plan')</td>
    </tr>
    <tr>
        <td>plots</td>
        <td>
           The list of plots to be displayed. ('points', 'centroids', 'hlm'
            or 'delay_max')
        </td>
        <td>array('points', 'hlm')</td>
    </tr>
    <tr>
        <td>fields</td>
        <td>
           Informations for the table. List of: 'time', 'outputrate-average',
            'delay-average', 'accepted-flows', 'rejected-flows',
            'timeslot-with-qos', 'timeslot-without-qos'
        </td>
        <td>array('time')</td>
    </tr>
</table>

## Full configuration

```php
$app->register(new KnowledgePlanServiceProvider(), array(
    'kp.cache_folder'        => __DIR__.'/cache',
    'kp.simulations_folder'  => __DIR__.'/simulations',
    'kp.options'             => array(
        'x_min'   => 0,
        'x_max'   => 8,
        'y_min'   => 0,
        'y_max'   => 60,
        'x_name'  => 'outputrate',
        'y_name'  => 'delay',
        'x_label' => 'Outpute rate (packet/ms)',
        'y_label' => 'Delay (ms)'
    ),
    'kp.simulation_options' => array(
        'knowledge_plan' => array(
            'x_max' => 8,
            'y_max' => 100
        ),
        'other_simulation' => array(
            'x_name' => 'foo',
            'y_name' => 'bar'
        )
    ),
    'kp.experiences' => array(
        'knowledgeplan' => array(
            'title'       => 'Modelling the Knowledge Plan',
            'simulations' => array('knowledge_plan'),
            'plots'       => array('centroids', 'points', 'hlm'), // 'hlm', 'centroids', 'delay_max' or 'points'
            'fields'      => array('time', 'knowledge-plan')
        ),
        'performance' => array(
            'title'               => 'Performance evaluation',
            'simulations'         => array(), // Empty to all
            'simulations-exclude' => array('knowledge_plan'),
            'plots'               => array('points', 'delay_max'), // 'hlm', 'centroids', 'delay_max' or 'points'
            'fields'              => array(
                'time', 'outputrate-average', 'delay-average',
                'accepted-flows', 'rejected-flows', 'timeslot-with-qos',
                'timeslot-without-qos'
            )
        )
    )
));
```