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
    'kp.force_fresh'  => false,
    'kp.cache_folder' => __DIR__.'/cache',
    'kp.output_file'  => __DIR__.'/output',
    'kp.plot_options' => array(
        'x_min'   => 0,
        'x_max'   => 10,
        'y_min'   => 0,
        'y_max'   => 60,
        'x_name'  => 'outputrate',
        'y_name'  => 'delay',
        'x_label' => 'Output rate',
        'y_label' => 'Delay (ms)'
    )
));
```
* __kp.cache_folder__: The folder to store simulation files
* __kp.outut_file__: The output file (e.g The simulation)
* __kp.force_fresh__: By default, the simulation is store in the cache to speed
up the following requests. If you want to always check the output file to
create the simulation and never call the cache, set this option to true.
The cache will always be ignored.
* __kp.plot_options__: Options for the plot
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
