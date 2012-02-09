KnowledgePlan website
=====================

Small guide to install the website (needs PHP 5.3.2 min)

### Installation

The website has several dependencies. To install these dependencies just run:

    php bin/vendors install

This script will download the dependencies in the `vendor` folder. For more
informations about Composer (the software to resolve these dependencies, see
https://github.com/composer/composer)

### Apache VirtualHost

The website needs a virtualhost pointing to the `web` folder. For instance:

```
<VirtualHost *:80>
    DocumentRoot "/path/to/KnowledgePlan/web"
    ServerName www.knowledgeplan.com
    <Directory "/path/to/KnowledgePlan/web">
        AllowOverride All
        Allow from All
    </Directory>
</VirtualHost>
```

### Configuration

All the configuration is done in the `bootstrap.php` with the
`KnowledgePlanServiceProvider`. Here is an example of all the options available:

```
// Knowledge Plan
$app->register(new Madalynn\KnowledgePlan\Silex\Provider\KnowledgePlanServiceProvider(), array(
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
            'title'       => 'Performance evaluation',
            'simulations' => array(),
            'plots'       => array('points', 'delay_max'), // 'hlm', 'centroids', 'delay_max' or 'points'
            'fields'      => array(
                'time', 'outputrate-average', 'delay-average',
                'accepted-flows', 'rejected-flows', 'timeslot-with-qos',
                'timeslot-without-qos'
            )
        )
    )
));
```
* __kp.cache_folder__: The folder to store simulation files
* __kp.simulations_folder__: The folder where the output files are stored.
* __kp.dry_run__: By default, the simulation is store in the cache to speed
up the following requests. If you want to always check the output file to
create the simulation and never call the cache, set this option to true.
The cache will always be ignored.
* __kp.options__: Options for _ALL_ the plot
    * __x_min__
    * __x_max__
    * __x_name__: The name of the field in the output file
    * __x_label__: The name of the label. If this field is left blank, the
    `x_name` field is using
    * __y_min__
    * __y_max__
    * __y_name__: The name of the field in the output file
    * __y_label__: The name of the label. If this field is left blank, the
    `y_name` field is using
* __kp.simulation_options__: Options for a specific simulation (associative
array). See `kp.options` for the list (it's the same)
* __kp.experiences__: The list of all the experiences
    * __title__: The title of the experience (will be in the black title bar)
    * __simulations__: An array of simulations
    * __plots__: The list of plots to be displayed ('points', 'centroids', 'hlm'
or 'delay_max')
    * __fields__: Informations for the table. List of: 'time',
'outputrate-average', 'delay-average', 'accepted-flows', 'rejected-flows',
'timeslot-with-qos', 'timeslot-without-qos'