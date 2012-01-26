KnowledgePlan website
=====================

Small guide to install the website (needs PHP 5.3.2 min)

### Installation

The website has several dependencies. To install these dependencies just run:

    php bin/vendors install

This script will download the dependencies in the `vendor` folder. For more
informations about Composer (the software to resolve these dependencies, see
https://github.com/composer/composer)

### Apache Virtualhost

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
    'kp.cache_folder' => __DIR__.'/cache',  // The folder to store the cache files
    'kp.output_file'  => __DIR__.'/output', // The simulation file
    'kp.plot_options' => array(             // Several options for the plot
        'x_min'  => 0,
        'x_max'  => 10,
        'y_min'  => 0,
        'y_max'  => 60,
        'x_name' => 'outputrate',
        'y_name' => 'delay'
    )
));
```